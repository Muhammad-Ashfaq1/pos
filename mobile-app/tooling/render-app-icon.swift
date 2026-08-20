#!/usr/bin/env swift
import AppKit
import Foundation

/// Rasterize the AutoServe SVG mark onto a square PNG (white canvas + padding).
/// Usage: render-app-icon.swift <svg> <out.png> <pixels> <pad-ratio>
/// pad-ratio 0.12 = 12% margin on each side (good for app icons).
/// pad-ratio 0.22 = maskable safe-zone.

if CommandLine.arguments.count < 5 {
    fputs("usage: render-app-icon.swift <svg> <png> <pixels> <pad-ratio>\n", stderr)
    exit(1)
}

let svgPath = CommandLine.arguments[1]
let outPath = CommandLine.arguments[2]
guard let pixels = Int(CommandLine.arguments[3]), pixels > 0 else {
    fputs("pixels must be a positive integer\n", stderr)
    exit(1)
}
guard let padRatio = Double(CommandLine.arguments[4]), padRatio >= 0, padRatio < 0.45 else {
    fputs("pad-ratio must be between 0 and 0.45\n", stderr)
    exit(1)
}

guard let svg = NSImage(contentsOf: URL(fileURLWithPath: svgPath)) else {
    fputs("could not load SVG: \(svgPath)\n", stderr)
    exit(1)
}

guard let bitmap = NSBitmapImageRep(
    bitmapDataPlanes: nil,
    pixelsWide: pixels,
    pixelsHigh: pixels,
    bitsPerSample: 8,
    samplesPerPixel: 4,
    hasAlpha: true,
    isPlanar: false,
    colorSpaceName: .deviceRGB,
    bytesPerRow: 0,
    bitsPerPixel: 0
) else {
    fputs("could not allocate bitmap\n", stderr)
    exit(1)
}

bitmap.size = NSSize(width: pixels, height: pixels)
NSGraphicsContext.saveGraphicsState()
guard let context = NSGraphicsContext(bitmapImageRep: bitmap) else {
    fputs("could not create graphics context\n", stderr)
    exit(1)
}
NSGraphicsContext.current = context
context.imageInterpolation = .high

NSColor.white.setFill()
NSRect(x: 0, y: 0, width: pixels, height: pixels).fill()

let pad = CGFloat(Double(pixels) * padRatio)
let logoRect = NSRect(
    x: pad,
    y: pad,
    width: CGFloat(pixels) - pad * 2,
    height: CGFloat(pixels) - pad * 2
)
svg.draw(
    in: logoRect,
    from: NSRect(origin: .zero, size: svg.size),
    operation: .sourceOver,
    fraction: 1.0,
    respectFlipped: true,
    hints: [.interpolation: NSNumber(value: NSImageInterpolation.high.rawValue)]
)

NSGraphicsContext.restoreGraphicsState()

guard let png = bitmap.representation(using: .png, properties: [:]) else {
    fputs("could not encode PNG\n", stderr)
    exit(1)
}

do {
    try png.write(to: URL(fileURLWithPath: outPath), options: .atomic)
} catch {
    fputs("could not write \(outPath): \(error)\n", stderr)
    exit(1)
}
