import csv
import json
import time
from pathlib import Path


def col_name(index):
    index += 1
    name = ""
    while index:
        index, remainder = divmod(index - 1, 26)
        name = chr(65 + remainder) + name
    return name


def read_grouped(path):
    grouped = {}
    with path.open("r", encoding="utf-8-sig", newline="") as handle:
        reader = csv.reader(handle, delimiter=";")
        raw_headers = next(reader)
        headers = {
            col_name(index): (header.strip() or f"Columna {col_name(index)}")
            for index, header in enumerate(raw_headers)
        }

        for line_number, row in enumerate(reader, start=2):
            if len(row) < 3:
                continue

            numero_predial = row[2].strip()
            if not numero_predial:
                continue

            values = {
                col_name(index): (row[index].strip() if index < len(row) else "")
                for index in range(len(raw_headers))
            }

            item = grouped.setdefault(numero_predial, {"headers": headers, "rows": []})
            item["rows"].append({"row_number": line_number, "values": values})

    return grouped


def main():
    root = Path(__file__).resolve().parents[4]
    csv1 = root / "dat_neiva" / "CATASTRAL_REGISTRO1_20250505.csv"
    csv2 = root / "dat_neiva" / "CATASTRAL_REGISTRO2_20250505.csv"
    output_dir = root / "dat_neiva" / "indice_predial"
    output_dir.mkdir(parents=True, exist_ok=True)

    start = time.time()
    print("Leyendo Registro 1...")
    registro1 = read_grouped(csv1)
    print(f"Registro 1: {len(registro1)} predios")

    print("Leyendo Registro 2...")
    registro2 = read_grouped(csv2)
    print(f"Registro 2: {len(registro2)} predios")

    predios = sorted(set(registro1) | set(registro2))
    metadata = {
        "version": "indice-predial-v1",
        "generated_at": time.strftime("%Y-%m-%d %H:%M:%S"),
        "registro1_csv": csv1.name,
        "registro2_csv": csv2.name,
        "total_predios": len(predios),
    }

    (output_dir / "metadata.json").write_text(
        json.dumps(metadata, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    for index, numero_predial in enumerate(predios, start=1):
        bucket = output_dir / (numero_predial[:6] or "sin_id")
        bucket.mkdir(parents=True, exist_ok=True)

        payload = {
            "numero_predial": numero_predial,
            "registro1": registro1.get(numero_predial, {"headers": {}, "rows": []}),
            "registro2": registro2.get(numero_predial, {"headers": {}, "rows": []}),
        }

        (bucket / f"{numero_predial}.json").write_text(
            json.dumps(payload, ensure_ascii=False, separators=(",", ":")),
            encoding="utf-8",
        )

        if index % 10000 == 0:
            print(f"Escritos {index}/{len(predios)}")

    elapsed = time.time() - start
    print(f"Indice generado en {output_dir} con {len(predios)} predios en {elapsed:.1f}s")


if __name__ == "__main__":
    main()
