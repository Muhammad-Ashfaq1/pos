#!/usr/bin/env python3
"""Wrap a PNG in an ICO container (Vista+ PNG-in-ICO)."""
from __future__ import annotations

import struct
import sys
from pathlib import Path


def main() -> int:
    if len(sys.argv) != 3:
        print("usage: png-to-ico.py <in.png> <out.ico>", file=sys.stderr)
        return 1

    png = Path(sys.argv[1]).read_bytes()
    if png[:8] != b"\x89PNG\r\n\x1a\n":
        print("input is not a PNG", file=sys.stderr)
        return 1

    # IHDR: width/height at bytes 16-23 of the file (8 sig + 4 len + 4 type).
    width = int.from_bytes(png[16:20], "big")
    height = int.from_bytes(png[20:24], "big")
    entry_w = 0 if width >= 256 else width
    entry_h = 0 if height >= 256 else height

    header = struct.pack("<HHH", 0, 1, 1)
    entry = struct.pack(
        "<BBBBHHII",
        entry_w,
        entry_h,
        0,
        0,
        1,
        32,
        len(png),
        6 + 16,
    )
    Path(sys.argv[2]).write_bytes(header + entry + png)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
