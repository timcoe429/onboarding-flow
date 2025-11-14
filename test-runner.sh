#!/bin/bash
# Test Runner Script
# Runs tests before allowing commit

echo "🧪 Running Docket Onboarding Tests..."
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer not found. Please install Composer first."
    echo "   Visit: https://getcomposer.org/download/"
    exit 1
fi

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
    echo "📦 Installing dependencies..."
    composer install
fi

# Run PHPUnit tests
composer test

# Capture exit code
TEST_RESULT=$?

if [ $TEST_RESULT -ne 0 ]; then
    echo ""
    echo "❌ Tests failed! Please fix errors before committing."
    echo ""
    echo "💡 Tips:"
    echo "   - Check test output above for specific failures"
    echo "   - Run 'composer test:watch' for detailed output"
    echo "   - Ensure all WordPress function mocks are set up correctly"
    echo ""
    exit 1
else
    echo ""
    echo "✅ All tests passed!"
    echo ""
    exit 0
fi

