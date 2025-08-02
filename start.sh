#!/bin/bash

# Start PHP backend on port 3001 in background
php -S 0.0.0.0:3001 -t college-monitor &

# Start Next.js frontend on port 3000
npm install
npm run build
npm start
