-- BetaKos: simpan URL Google Maps yang diberikan pemilik secara opsional.
-- Koordinat tetap disimpan untuk pencarian/jarak. Jika pemilik memakai lokasi saat ini,
-- google_maps_url dibiarkan NULL. Jalankan sekali pada database platform_kos.
ALTER TABLE kos
    ADD COLUMN google_maps_url VARCHAR(2048) NULL AFTER longitude;

CREATE INDEX idx_kos_google_maps_url ON kos (google_maps_url(191));
