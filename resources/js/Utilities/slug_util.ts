export default function slugUtil(text: string) {
    return text
        .toLowerCase() // Ubah semua huruf menjadi huruf kecil
        .trim() // Hapus spasi di awal dan akhir
        .replace(/[\s_]+/g, '') // Ganti spasi atau underscore dengan tanda -
        .replace(/[^\w-]+/g, ''); // Hapus karakter yang bukan huruf, angka, atau tanda -
}