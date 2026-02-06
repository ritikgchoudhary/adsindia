# Build Instructions for Production

## Problem
If you're getting 404 errors on routes like `/user/login`, it means the Vue.js app hasn't been built for production yet.

## Solution: Build Vue.js App

### Step 1: Install Dependencies
```bash
cd /www/wwwroot/a22-com.site/frontend
npm install
```

### Step 2: Build for Production
```bash
npm run build
```

This will create all necessary files in the `../public/` directory.

### Step 3: Verify Build
```bash
ls -la ../public/
```

You should see:
- `index.html`
- `assets/` directory with CSS and JS files

### Step 4: Set Permissions
```bash
chmod -R 755 ../public
chown -R www:www ../public
```

## What Gets Built

After running `npm run build`, the following structure will be created:

```
/www/wwwroot/a22-com.site/
├── public/
│   ├── index.html          # Main Vue.js app entry point
│   └── assets/
│       ├── index-[hash].js  # Vue.js application bundle
│       └── index-[hash].css # Styles
```

## How It Works

1. **Web Server** (Apache/Nginx) receives request for `/user/login`
2. **.htaccess** (Apache) or **nginx config** routes it to `index.php`
3. **index.php** checks if it's an API route:
   - If `/api/*`, `/ipn/*`, etc. → Goes to Laravel
   - Otherwise → Serves `public/index.html` (Vue.js app)
4. **Vue Router** handles `/user/login` and shows the Login component

## Troubleshooting

### Still Getting 404?

1. **Check if public/index.html exists:**
   ```bash
   ls -la /www/wwwroot/a22-com.site/public/index.html
   ```

2. **Rebuild the app:**
   ```bash
   cd /www/wwwroot/a22-com.site/frontend
   rm -rf ../public/*
   npm run build
   ```

3. **Check web server configuration:**
   - Apache: Make sure `.htaccess` is being read
   - Nginx: Check if `try_files` is configured correctly

4. **Check file permissions:**
   ```bash
   chmod 644 /www/wwwroot/a22-com.site/public/index.html
   chmod -R 755 /www/wwwroot/a22-com.site/public/assets
   ```

## Development vs Production

### Development
- Use `npm run dev` - Vite dev server on port 3000
- Hot reload enabled
- Access at `http://localhost:3000`

### Production
- Use `npm run build` - Creates static files
- Files served by web server (Apache/Nginx)
- Access via domain (e.g., `https://adsskillindia.in`)

## Quick Build Command

```bash
cd /www/wwwroot/a22-com.site/frontend && npm install && npm run build
```
