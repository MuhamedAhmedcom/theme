#!/bin/bash
set -e

# Build production asset
INPUT="src/assets/v18/home.js"
OUTPUT="dist/assets/v18/home.js"

mkdir -p dist/assets/v18

# Copy to dist (production uses unminified with manual optimization)
cp "$INPUT" "$OUTPUT"

# Generate SHA-256 hash
SHA256=$(sha256sum "$OUTPUT" | cut -d' ' -f1)

echo "Production asset: $OUTPUT"
echo "SHA-256: $SHA256"
echo ""
echo "File size comparison:"
stat -c "%s bytes" /home/ubuntu/.cursor/projects/workspace/uploads/contact-final-live_fdec.js | sed 's/^/  Original: /'
stat -c "%s bytes" "$OUTPUT" | sed 's/^/  Fixed:    /'
