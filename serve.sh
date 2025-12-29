#!/bin/bash
# Laravel Development Server for www.asl.com

echo "🚀 Starting Laravel server on http://www.asl.com (port 80)..."
sudo php artisan serve --port=80 --host=0.0.0.0
