# Changes Not Showing on Live? Follow This

## Step 1: Check if the live site is THIS server

Open both in browser: **https://adsskillindia.in/version.txt** and **https://adsskillindia.in/api/deploy-check**

- If you see **LIVE_BUILD=** with **today’s date/time** → The site is served from this server. Go to Step 2.
- If **either** gives **404** or **old date** → The domain is not pointing to this server. Point **adsskillindia.in** to this server’s folder (`/www/wwwroot/a22-com.site` as document root) or copy the built `public/` folder to where the live site runs.

## Step 2: Force your browser to load the latest files

1. **Hard refresh**
   - Windows/Linux: `Ctrl + Shift + R`
   - Mac: `Cmd + Shift + R`

2. **Or use Incognito/Private window**
   - Open a new incognito window and go to:  
     **https://adsskillindia.in/user/courses**

3. **Or clear cache**
   - Chrome: Settings → Privacy → Clear browsing data → Cached images and files (last hour or day).

## Step 3: After every code/build change

1. Run build:
   ```bash
   cd /www/wwwroot/a22-com.site/frontend && npm run build
   ```
2. Check **version.txt** again (Step 1) to confirm the new build time.
3. Do Step 2 (hard refresh or incognito) on **/user/courses**.

## If you use Nginx

Add no-cache for the SPA so HTML is not cached:

```nginx
location = /index.html {
    add_header Cache-Control "no-cache, no-store, must-revalidate";
    add_header Pragma "no-cache";
}
```

Then: `sudo nginx -t && sudo systemctl reload nginx`
