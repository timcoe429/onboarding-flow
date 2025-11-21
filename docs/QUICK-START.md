# Quick Start Guide

**Works on Mac and Windows!**

## For Testing in Browser (Docker)

### First Time Setup (5 minutes)

1. **Install Docker Desktop** (one-time)
   - **Mac/Windows**: https://www.docker.com/products/docker-desktop/
   - Download, install, and open it

2. **Start WordPress**
   - **Mac**: Double-click `start-wordpress.sh`
   - **Windows**: Double-click `start-wordpress.bat`
   - Or run: `docker-compose up -d`

3. **Set Up WordPress** (one-time)
   - Open: http://localhost:8080
   - Choose language → Continue
   - Fill out form (Site Title, Username, Password, Email)
   - Click "Install WordPress" → Log in

4. **Activate Plugin**
   - Go to Plugins → Find "Docket Onboarding" → Activate

5. **Create Test Page**
   - Pages → Add New
   - Title: `Onboarding Form`
   - Add shortcode: `[docket_onboarding]`
   - Publish → View Page

**🎉 Done! The form should appear!**

### Daily Use

**Start**: `docker-compose up -d` or double-click `start-wordpress.sh`/`start-wordpress.bat`  
**Stop**: `docker-compose down` or double-click `stop-wordpress.sh`/`stop-wordpress.bat`  
**Access**: http://localhost:8080

**Making Changes**: Edit files → Save → Refresh browser → See changes immediately!

**Full guide**: See `DOCKER-SETUP.md`

---

## For Automated Testing (No Browser)

**Run tests before committing:**

```bash
composer test
```

**Full guide**: See `README-TESTING.md`

---

## Which Should I Use?

- **Making code changes?** → Run `composer test` first
- **Testing in browser?** → Use Docker (`docker-compose up -d`)
- **Before committing?** → Always run `composer test`

