<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

trait Sortable
{
    /**
     * Tambahkan di model:
     * protected array $sortable = ['id', 'created_at', 'user.name', 'customer.company'];
     */

    /**
     * Scope utama sorting aman (kolom normal & relasi multi-level).
     */
    public function scopeSort(Builder $query, ?string $sortField, ?string $sortDirection = 'asc'): Builder
    {
        $dir = strtolower((string) $sortDirection);
        if (!$sortField || !in_array($dir, ['asc', 'desc'], true)) {
            return $query;
        }

        // Whitelist kolom yang boleh disort
        if (!in_array($sortField, $this->sortable ?? [], true)) {
            return $query;
        }

        // Pastikan select base table * dulu agar tidak ambiguous
        $this->ensureBaseSelect($query);

        // Kasus kolom normal (tanpa relasi)
        if (!Str::contains($sortField, '.')) {
            return $query->orderBy($this->qualifyColumn($sortField), $dir);
        }

        // Kasus relasi multi-level: "relation1.relation2.column"
        // Tapi jika column adalah foreign key (id), langsung orderBy tanpa join
        if ($this->isForeignKeyColumn($sortField)) {
            return $query->orderBy($this->qualifyColumn($this->getForeignKeyFromSortField($sortField)), $dir);
        }

        return $this->applyNestedSort($query, $sortField, $dir);
    }

    /**
     * Handle sorting untuk relasi bersarang (nested relations).
     */
    protected function applyNestedSort(Builder $query, string $sortField, string $dir): Builder
    {
        $parts = explode('.', $sortField);
        $column = array_pop($parts); // Ambil kolom terakhir
        $relations = $parts; // Array relasi: ['relation1', 'relation2', ...]

        if (empty($relations)) {
            return $query;
        }

        // Jika hanya single relation, coba gunakan subquery approach (lebih efisien)
        if (count($relations) === 1) {
            $relation = $relations[0];
            if (method_exists($this, $relation)) {
                $relationInstance = $this->$relation();
                $related = $relationInstance->getRelated();
                $relatedTable = $related->getTable();

                // Untuk BelongsTo, gunakan subquery
                if ($relationInstance instanceof BelongsTo) {
                    $fk = $relationInstance->getForeignKeyName();
                    $ownerKey = $relationInstance->getOwnerKeyName();

                    $subQuery = $related::select($column)
                        ->whereColumn("{$relatedTable}.{$ownerKey}", "{$this->getTable()}.{$fk}")
                        ->limit(1);

                    return $query->orderBy($subQuery, $dir);
                }

                // Untuk HasOne/HasMany, gunakan subquery dengan first record
                if ($relationInstance instanceof HasOne || $relationInstance instanceof HasMany) {
                    $fk = $relationInstance->getForeignKeyName();
                    $localKey = $relationInstance->getLocalKeyName();

                    $subQuery = $related::select($column)
                        ->whereColumn("{$relatedTable}.{$fk}", "{$this->getTable()}.{$localKey}")
                        ->orderBy('id', 'asc') // Ambil first record
                        ->limit(1);

                    return $query->orderBy($subQuery, $dir);
                }
            }
        }

        // Fallback ke join approach untuk nested relations atau jika subquery gagal
        return $this->applyNestedSortWithJoin($query, $sortField, $dir);
    }

    /**
     * Handle sorting untuk relasi bersarang menggunakan JOIN (fallback).
     */
    protected function applyNestedSortWithJoin(Builder $query, string $sortField, string $dir): Builder
    {
        $parts = explode('.', $sortField);
        $column = array_pop($parts); // Ambil kolom terakhir
        $relations = $parts; // Array relasi: ['relation1', 'relation2', ...]

        if (empty($relations)) {
            return $query;
        }

        // Build nested joins step by step
        $currentModel = $this;
        $currentAlias = null; // Start with no alias (base table)

        foreach ($relations as $index => $relation) {
            if (!method_exists($currentModel, $relation)) {
                return $query; // Relasi tidak ada, skip
            }

            $relationInstance = $currentModel->$relation();
            $related = $relationInstance->getRelated();
            $relatedTable = $related->getTable();

            // Alias unik untuk setiap level
            $alias = implode('_', array_slice($relations, 0, $index + 1)) . '_sort';

            // Join dengan alias ini
            $this->joinOnce($query, $relatedTable, $alias, $relationInstance, $currentAlias);

            // Update untuk level berikutnya
            $currentModel = $related;
            $currentAlias = $alias;
        }

        // ORDER BY dengan alias terakhir
        return $query->orderBy("{$currentAlias}.{$column}", $dir);
    }

    /**
     * Pastikan SELECT hanya baseTable.* kalau sebelumnya masih '*'
     * atau ada 'id' tanpa prefix. Ini mencegah "Column 'id' is ambiguous".
     */
    protected function ensureBaseSelect(Builder $query): void
    {
        $base = $this->getTable();
        $columns = $query->getQuery()->columns;

        $isStar = function ($cols) {
            if ($cols === null) return true;                // belum ada select → default *
            if (!is_array($cols)) return false;
            return count($cols) === 1 && $cols[0] === '*';
        };

        if ($isStar($columns)) {
            $query->select("{$base}.*");
            return;
        }

        // Kalau sudah ada select tapi ada 'id' telanjang, qualify cepat
        if (is_array($columns)) {
            $qualified = [];
            foreach ($columns as $c) {
                // Bentuk bisa berupa string atau Expression; kita cuma cover string umum
                if (is_string($c) && $c === 'id') {
                    $qualified[] = "{$base}.id";
                } else {
                    $qualified[] = $c;
                }
            }
            $query->getQuery()->columns = $qualified;
        }
    }

    protected function joinOnce(Builder $query, string $relatedTable, string $alias, $relationInstance, string $parentAlias = null): void
    {
        if ($this->alreadyJoined($query, $alias)) {
            return;
        }

        $parentTable = $parentAlias ?: $this->getTable();

        // Extract relation name from alias (reverse engineer)
        $relation = str_replace('_sort', '', $alias);

        // BelongsTo
        if ($relationInstance instanceof BelongsTo) {
            $fk       = $relationInstance->getForeignKeyName();   // unqualified di parent
            $ownerKey = $relationInstance->getOwnerKeyName();     // unqualified di related
            $left = "{$parentTable}.{$fk}";
            $right = "{$alias}.{$ownerKey}";
            $query->leftJoin("{$relatedTable} as {$alias}", $left, '=', $right);
            return;
        }

        // HasOne / HasMany
        if ($relationInstance instanceof HasOne || $relationInstance instanceof HasMany) {
            $fk       = $relationInstance->getForeignKeyName();   // unqualified di related
            $localKey = $relationInstance->getLocalKeyName();     // unqualified di parent
            $query->leftJoin("{$relatedTable} as {$alias}", "{$alias}.{$fk}", '=', "{$parentTable}.{$localKey}");
            return;
        }

        // Fallback generic (untuk tipe lain, misal Morph dsb.)
        // Simple approach: assume standard foreign key naming
        $leftKey = $relation . '_id';  // e.g., 'division_id'
        $rightKey = 'id';              // assume primary key

        if ($parentAlias) {
            $leftTable = $parentAlias;
        } else {
            $leftTable = $this->getTable();
        }

        $query->leftJoin("{$relatedTable} as {$alias}", "{$leftTable}.{$leftKey}", '=', "{$alias}.{$rightKey}");
    }

    /**
     * Deteksi apakah alias sudah pernah di-join agar tidak duplikat.
     */
    protected function alreadyJoined(Builder $query, string $alias): bool
    {
        $joins = $query->getQuery()->joins ?? [];
        foreach ($joins as $join) {
            if (isset($join->table) && is_string($join->table)) {
                // Check if table contains "as alias" or exactly alias
                if (strpos($join->table, " as {$alias}") !== false || $join->table === $alias) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Cek apakah sortField adalah foreign key column (relation.id)
     */
    protected function isForeignKeyColumn(string $sortField): bool
    {
        $parts = explode('.', $sortField);
        if (count($parts) !== 2) {
            return false;
        }

        [$relation, $column] = $parts;
        return $column === 'id' && method_exists($this, $relation);
    }

    /**
     * Dapatkan nama foreign key column dari sortField (relation.id -> relation_id)
     */
    protected function getForeignKeyFromSortField(string $sortField): string
    {
        [$relation] = explode('.', $sortField);
        $relationInstance = $this->$relation();

        if ($relationInstance instanceof BelongsTo) {
            return $relationInstance->getForeignKeyName();
        }

        // Untuk tipe lain, assume standard naming
        return $relation . '_id';
    }

}
