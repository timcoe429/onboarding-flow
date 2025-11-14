#!/bin/bash

# Simple script to start WordPress
# Just double-click this file or run: ./start-wordpress.sh

echo "🚀 Starting WordPress..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running!"
    echo "Please open Docker Desktop and wait for it to start."
    echo "Then try again."
    exit 1
fi

# Start WordPress
docker-compose up -d

# Wait a moment
sleep 3

# Check if it's running
if docker-compose ps | grep -q "Up"; then
    echo "✅ WordPress is starting!"
    echo ""
    echo "🌐 Open your browser and go to:"
    echo "   http://localhost:8080"
    echo ""
    echo "📝 To stop WordPress, run:"
    echo "   docker-compose down"
    echo ""
    echo "Or double-click: stop-wordpress.sh"
else
    echo "❌ Something went wrong. Check the logs:"
    echo "   docker-compose logs wordpress"
fi

