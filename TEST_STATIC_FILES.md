# Test Static Files

## Quick Test Commands

### Test CSS file:
```bash
curl -I https://adsskillindia.in/assets/index.css
```

Expected response:
```
HTTP/1.1 200 OK
Content-Type: text/css
```

### Test JS file:
```bash
curl -I https://adsskillindia.in/assets/index-CRGo-EFB.js
```

Expected response:
```
HTTP/1.1 200 OK
Content-Type: application/javascript
```

## If Files Still Return HTML

1. **Restart Apache:**
   ```bash
   sudo systemctl restart apache2
   # or
   sudo service apache2 restart
   ```

2. **Check if mod_rewrite is enabled:**
   ```bash
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

3. **Check if mod_headers is enabled:**
   ```bash
   sudo a2enmod headers
   sudo systemctl restart apache2
   ```

4. **Verify .htaccess is being read:**
   Add this to your Apache virtual host config:
   ```apache
   <Directory "/www/wwwroot/a22-com.site">
       AllowOverride All
       Require all granted
   </Directory>
   ```

5. **Check Apache error logs:**
   ```bash
   tail -f /var/log/apache2/error.log
   ```

## Alternative: Direct File Access Test

Test if files are accessible directly:
```bash
# Should return CSS content
curl https://adsskillindia.in/public/assets/index.css

# Should return JS content  
curl https://adsskillindia.in/public/assets/index-CRGo-EFB.js
```

If these work but `/assets/` doesn't, the rewrite rule needs adjustment.
