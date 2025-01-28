#!/bin/bash

# Navigate to the webapp directory
cd /var/www/html/

# Pull the latest changes
git reset --hard  # Optional: ensures no local changes conflict
git pull origin main  # Replace 'main' with your default branch if different

# Set proper permissions (if needed)
sudo chown -R www-data:www-data /var/www/html/
sudo chmod -R 755 /var/www/html/

echo "Webapp updated successfully."
