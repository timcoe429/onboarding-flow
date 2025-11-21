# Docker Setup - Simple Guide

This guide will help you run the Docket Onboarding plugin locally using Docker. **Works on Mac and Windows!** **No technical knowledge required!**

## What You Need

- **Docker Desktop** installed on your computer
  - **Mac**: Download from https://www.docker.com/products/docker-desktop/
  - **Windows**: Download from https://www.docker.com/products/docker-desktop/
  - Install it (just like any other app)
  - Make sure it's running:
    - **Mac**: Docker icon in menu bar (should be green/running)
    - **Windows**: Docker icon in system tray (should be running)

## Quick Start (3 Steps!)

### Step 1: Install Docker Desktop
- Go to https://www.docker.com/products/docker-desktop/
- Download and install Docker Desktop for your operating system
- Open Docker Desktop and wait for it to start

### Step 2: Start WordPress

**Option A: Use the Scripts (Easiest)**
- **Mac/Linux**: Double-click `start-wordpress.sh`
- **Windows**: Double-click `start-wordpress.bat`

**Option B: Use Command Line**
- **Mac/Linux**: Open Terminal in this folder
- **Windows**: Open Command Prompt or PowerShell in this folder
- Run:
```bash
docker-compose up -d
```

**What this does**: Starts WordPress and a database in the background

**Wait for**: You'll see messages like "Creating..." and "Starting..." - wait until it says "done" or stops showing messages (about 30 seconds)

### Step 3: Open WordPress
Open your web browser and go to:

**http://localhost:8080**

You'll see the WordPress installation screen!

## WordPress Setup (One-Time)

### 1. Choose Language
- Select your language and click "Continue"

### 2. Fill Out the Form
- **Site Title**: `Docket Onboarding Test` (or anything you want)
- **Username**: `admin` (or your choice)
- **Password**: `admin` (or your choice - make it something you'll remember!)
- **Your Email**: Your email address
- **Search Engine Visibility**: Leave unchecked (we want it visible for testing)

### 3. Click "Install WordPress"

### 4. Log In
- Click "Log In"
- Enter your username and password
- Click "Log In"

**You're now in WordPress!** 🎉

## Activate the Plugin

### 1. Go to Plugins
- In the left sidebar, click **"Plugins"**

### 2. Find "Docket Onboarding"
- You should see "Docket Onboarding" in the list
- Click **"Activate"** under it

**Done!** The plugin is now active.

## Create a Test Page

### 1. Create New Page
- In the left sidebar, click **"Pages"** → **"Add New"**

### 2. Add the Form
- **Title**: `Onboarding Form` (or anything)
- In the content area, type: `[docket_onboarding]`
- Click **"Publish"** (top right)

### 3. View the Form
- Click **"View Page"** (or the page title)
- You should see the onboarding form!

## Daily Use

### Starting WordPress (Every Time You Want to Test)

**Easy Way:**
- **Mac/Linux**: Double-click `start-wordpress.sh`
- **Windows**: Double-click `start-wordpress.bat`

**Command Line Way:**
```bash
docker-compose up -d
```

Then open: **http://localhost:8080**

### Stopping WordPress (When You're Done)

**Easy Way:**
- **Mac/Linux**: Double-click `stop-wordpress.sh`
- **Windows**: Double-click `stop-wordpress.bat`

**Command Line Way:**
```bash
docker-compose down
```

**That's it!** WordPress stops running.

### Viewing Logs (If Something Goes Wrong)

```bash
docker-compose logs wordpress
```

This shows any error messages.

### Debugging Tips

**Enable WordPress Debug Mode** (for more detailed errors):
1. Access WordPress files in Docker: `docker-compose exec wordpress bash`
2. Edit `wp-config.php`: `nano /var/www/html/wp-config.php`
3. Add these lines before "That's all, stop editing!":
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```
4. Errors will be logged to `wp-content/debug.log`

**Check Browser Console**:
- Open DevTools (F12) → Console tab
- Look for JavaScript errors
- Check Network tab for AJAX requests

**Test API Integration**:
- Go to WordPress Admin → Settings → Docket Cloner
- Check "Disable API Calls" to test form submission without remote API
- Uncheck to test full integration

## Common Tasks

### Access WordPress Admin
- Go to: **http://localhost:8080/wp-admin**
- Log in with your username and password

### Make Changes to Plugin Code
1. Edit files in `docket-onboarding/` folder
2. Refresh your browser page
3. Changes appear immediately!

### Reset Everything (Start Fresh)

```bash
docker-compose down -v
docker-compose up -d
```

**Warning**: This deletes all your WordPress data and you'll need to set it up again!

## Troubleshooting

### "Docker is not running"
- **Mac**: Open Docker Desktop, wait for icon in menu bar to be green
- **Windows**: Open Docker Desktop, check system tray icon is running
- Try again

### "Port 8080 is already in use"
- Something else is using port 8080
- Edit `docker-compose.yml` and change `8080:80` to `8081:80` (or any other number)
- Then run `docker-compose up -d` again
- Use `http://localhost:8081` instead

### "Can't connect to database"
- Make sure Docker Desktop is running
- Try: `docker-compose restart`
- Wait 30 seconds and try again

### "Plugin not showing"
- Make sure you're in the right folder (the one with `docker-compose.yml`)
- Check that `docket-onboarding` folder exists
- Try: `docker-compose restart wordpress`

### "Changes not showing"
- Hard refresh your browser: `Cmd+Shift+R` (Mac) or `Ctrl+Shift+R` (Windows)
- Check browser console for errors (F12 → Console tab)

### Windows-Specific Issues

**"Scripts won't run"**
- Make sure you're using `start-wordpress.bat` (not `.sh`)
- If double-click doesn't work, right-click → "Run as administrator"

**"Path issues"**
- Make sure you're in the project folder when running commands
- Use Command Prompt or PowerShell (both work)

**"Docker Desktop won't start"**
- Make sure WSL 2 is installed (Windows 10/11)
- Docker Desktop will prompt you to install it if needed
- Restart your computer after installing WSL 2

## What's Happening Behind the Scenes

Don't worry about this, but if you're curious:

- **WordPress** runs in a container (like a virtual computer)
- **Database** runs in another container
- **Your plugin** is linked from your computer into WordPress
- Everything runs on **port 8080** on your computer
- Data is saved in Docker volumes (so it persists between restarts)

## Quick Reference

| What You Want | Mac/Linux | Windows |
|--------------|-----------|---------|
| Start WordPress | Double-click `start-wordpress.sh` or `docker-compose up -d` | Double-click `start-wordpress.bat` or `docker-compose up -d` |
| Stop WordPress | Double-click `stop-wordpress.sh` or `docker-compose down` | Double-click `stop-wordpress.bat` or `docker-compose down` |
| View logs | `docker-compose logs wordpress` | `docker-compose logs wordpress` |
| Restart | `docker-compose restart` | `docker-compose restart` |
| Start fresh | `docker-compose down -v` then `docker-compose up -d` | `docker-compose down -v` then `docker-compose up -d` |

## Need Help?

1. **Check Docker is running**: 
   - **Mac**: Look for Docker icon in menu bar (should be green)
   - **Windows**: Look for Docker icon in system tray
2. **Check logs**: `docker-compose logs wordpress`
3. **Restart everything**: `docker-compose restart`
4. **Start completely fresh**: `docker-compose down -v` then `docker-compose up -d`

## Platform Compatibility

✅ **Works on:**
- Mac (Intel and Apple Silicon/M1/M2/M3)
- Windows 10/11
- Linux

The Docker configuration uses `platform: linux/amd64` which:
- Runs natively on Windows (Intel/AMD)
- Runs with emulation on Mac (Docker Desktop handles this automatically)
- Works consistently across all platforms

## That's It!

You now have WordPress running locally with the Docket Onboarding plugin installed. Just remember:

- **Start**: `docker-compose up -d`
- **Stop**: `docker-compose down`
- **Access**: http://localhost:8080

Happy testing! 🚀

