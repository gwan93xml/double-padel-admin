"use client";

import React, { useState, useEffect } from "react";
import axios from "axios";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "./ui/select";

export interface WarehouseType {
  id: string;
  code: string;
  name: string;
  // ...any other fields your API returns
}

type SelectWarehouseProps = {
  onChange: (warehouse: WarehouseType) => void;
  value?: WarehouseType;
};

export default function SelectWarehouse({
  onChange,
  value,
}: SelectWarehouseProps) {
  const [warehouses, setWarehouses] = useState<WarehouseType[]>([]);
  const [loading, setLoading] = useState(false);

  useEffect(() => {
    setLoading(true);
    axios
      .get<{ data: WarehouseType[] }>("/admin/warehouse/list")
      .then((res) => {
        setWarehouses(res.data.data);
      })
      .catch((err) => {
        console.error("Failed to load warehouses:", err);
      })
      .finally(() => setLoading(false));
  }, []);

  return (
    <Select
      value={value?.id ?? ""}
      onValueChange={(val) => {
        const selected = warehouses.find((w) => String(w.id) === val);
        if (selected) onChange(selected);
      }}
    >
      <SelectTrigger>
        <SelectValue placeholder={loading ? "Loading..." : "Pilih Gudang"} />
      </SelectTrigger>
      <SelectContent>
        {warehouses.map((w) => (
          <SelectItem key={w.id} value={String(w.id)}>
            {w.code} — {w.name}
          </SelectItem>
        ))}
        {warehouses.length === 0 && !loading && (
          <SelectItem disabled value="">
            Tidak ada gudang
          </SelectItem>
        )}
      </SelectContent>
    </Select>
  );
}
