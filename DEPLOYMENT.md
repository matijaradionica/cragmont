# Production Deployment Configuration

## Fixing "413 Request Entity Too Large" Error

The application allows users to upload topo diagrams and multiple photos when creating routes. To support these uploads, you need to configure your web server and PHP settings.

### Required Limits

- **Topo diagram**: 5MB max
- **Route photos**: Up to 10 photos, 5MB each
- **Total request size**: 64MB recommended (to handle all uploads + form data)

---

## Configuration by Web Server

### For Nginx

Edit your Nginx configuration file (usually `/etc/nginx/nginx.conf` or site-specific config in `/etc/nginx/sites-available/`):

```nginx
http {
    # Increase client body size limit
    client_max_body_size 64M;

    # Increase timeouts for large uploads
    client_body_timeout 300s;
    client_header_timeout 300s;

    # ... rest of your config
}
```

Or add it to your specific server block:

```nginx
server {
    listen 80;
    server_name yourdomain.com;

    # Upload size limit
    client_max_body_size 64M;
    client_body_timeout 300s;

    root /path/to/cragmont/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;

        # PHP upload limits for FastCGI
        fastcgi_read_timeout 300;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
    }
}
```

After editing, restart Nginx:
```bash
sudo nginx -t  # Test configuration
sudo systemctl restart nginx
```

### For Apache

The `.htaccess` file has been updated with PHP settings. If using Apache with mod_php, ensure these directives are allowed.

If the `.htaccess` settings don't work, edit your Apache VirtualHost configuration:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /path/to/cragmont/public

    <Directory /path/to/cragmont/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Upload limits
        php_value upload_max_filesize 64M
        php_value post_max_size 64M
        php_value max_execution_time 300
        php_value max_input_time 300
    </Directory>
</VirtualHost>
```

Restart Apache:
```bash
sudo apachectl configtest  # Test configuration
sudo systemctl restart apache2
```

---

## PHP Configuration

Edit your `php.ini` file (location varies by server):
- Ubuntu/Debian: `/etc/php/8.2/fpm/php.ini` (for PHP-FPM) or `/etc/php/8.2/apache2/php.ini` (for Apache)
- Check current location: `php --ini`

Update these values:

```ini
upload_max_filesize = 64M
post_max_size = 64M
max_execution_time = 300
max_input_time = 300
memory_limit = 256M
```

**Important**: If using PHP-FPM (common with Nginx), restart PHP-FPM after changes:
```bash
sudo systemctl restart php8.2-fpm
```

---

## Verification

After making these changes:

1. **Check PHP settings**:
   ```bash
   php -i | grep -E 'upload_max_filesize|post_max_size'
   ```

2. **Test upload**: Try creating a route with multiple large photos

3. **Check logs** if issues persist:
   - Nginx: `/var/log/nginx/error.log`
   - Apache: `/var/log/apache2/error.log`
   - PHP-FPM: `/var/log/php8.2-fpm.log`

---

## Cloudflare / Reverse Proxy

If using Cloudflare or another reverse proxy:

- **Cloudflare**: Free plan limits uploads to 100MB (sufficient)
- **Other proxies**: Check their upload size limits and adjust accordingly

---

## Quick Fix Commands

### Ubuntu/Debian with Nginx + PHP-FPM

```bash
# Edit PHP-FPM configuration
sudo nano /etc/php/8.2/fpm/php.ini
# Update: upload_max_filesize=64M, post_max_size=64M

# Edit Nginx configuration
sudo nano /etc/nginx/sites-available/your-site
# Add: client_max_body_size 64M;

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

### Ubuntu/Debian with Apache

```bash
# Edit PHP configuration
sudo nano /etc/php/8.2/apache2/php.ini
# Update: upload_max_filesize=64M, post_max_size=64M

# Restart Apache
sudo systemctl restart apache2
```
