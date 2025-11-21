#!/bin/bash

# Simple script to stop WordPress
# Just double-click this file or run: ./stop-wordpress.sh

echo "🛑 Stopping WordPress..."
echo ""

docker-compose down

echo ""
echo "✅ WordPress has been stopped."
echo ""
echo "To start it again, run:"
echo "   docker-compose up -d"
echo ""
echo "Or double-click: start-wordpress.sh"

