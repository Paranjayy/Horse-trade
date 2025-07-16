# Deploying Horse Trading Website to Render.com

## Prerequisites
- GitHub repository with your horse trading website
- Render.com account (free tier available)

## Step 1: Create PostgreSQL Database

1. **Login to Render Dashboard**
   - Go to [dashboard.render.com](https://dashboard.render.com)
   - Click "New" → "PostgreSQL"

2. **Configure Database**
   - **Name**: `horse-trading-db`
   - **Region**: Choose closest to your target users
   - **PostgreSQL Version**: Latest (15.x)
   - **Plan**: Free tier is sufficient for testing
   - Click "Create Database"

3. **Get Connection Details**
   - Once created, go to your database dashboard
   - Copy the **Internal Database URL** (starts with `postgresql://`)
   - Save this for later - you'll need it for environment variables

4. **Import Database Schema**
   - In the database dashboard, click "Connect" 
   - Use the provided connection string to connect with a PostgreSQL client
   - Run the `database_postgresql.sql` file to create tables and sample data

## Step 2: Create Web Service

1. **Create New Web Service**
   - From Render dashboard, click "New" → "Web Service"
   - Connect to your GitHub repository
   - Select the horse trading website repository

2. **Configure Service Settings**
   - **Name**: `horse-trading-website`
   - **Environment**: `Docker` (Render will auto-detect)
   - **Region**: Same as your database
   - **Branch**: `main`
   - **Root Directory**: Leave empty
   - **Build Command**: Leave empty
   - **Start Command**: 
     ```bash
     php -S 0.0.0.0:$PORT -t .
     ```

3. **Set Environment Variables**
   Add these environment variables in the service settings:
   ```
   DATABASE_URL=your_postgresql_connection_string_from_step_1
   DEMO_MODE=false
   ```

## Step 3: Deploy

1. **Deploy Service**
   - Click "Create Web Service"
   - Render will automatically deploy from your GitHub repository
   - First deployment may take 5-10 minutes

2. **Monitor Deployment**
   - Watch the deployment logs for any errors
   - Once complete, you'll get a live URL (e.g., `https://your-service.onrender.com`)

## Step 4: Important Notes

### Database Compatibility
- Your local MySQL database uses `mysqli` functions
- Production PostgreSQL uses PDO for compatibility
- The updated `includes/db.php` handles both automatically

### File Uploads
- Render has ephemeral storage - uploaded files don't persist
- For production, consider using:
  - **Cloudinary** for image hosting
  - **AWS S3** for file storage
  - **Firebase Storage** for files

### Environment Variables
The application automatically detects the environment:
- **Local**: Uses MySQL with localhost
- **Production**: Uses PostgreSQL with `DATABASE_URL`
- **Demo Mode**: Works without database (if needed)

## Step 5: Post-Deployment

1. **Test Your Website**
   - Visit your Render URL
   - Test user registration/login
   - Try browsing horses
   - Test adding a new horse listing
   - Verify search and filters work

2. **Custom Domain (Optional)**
   - In Render dashboard, go to your service settings
   - Add your custom domain under "Custom Domains"
   - Update DNS records as instructed

## Troubleshooting

### Common Issues:

1. **Database Connection Failed**
   - Check `DATABASE_URL` environment variable
   - Ensure database is running and accessible
   - Verify the connection string format

2. **PHP Errors**
   - Check deployment logs in Render dashboard
   - Ensure all PHP files use PDO syntax
   - Verify environment variables are set

3. **Images Not Loading**
   - Check if image paths are correct
   - Remember: uploaded images won't persist on Render
   - Consider implementing cloud storage for images

### Checking Logs:
- In Render dashboard → Your service → "Logs" tab
- Shows real-time application logs
- Useful for debugging database and PHP issues

## Alternative: Quick Start with Demo Mode

If you want to deploy quickly without setting up a database:

1. Set environment variable: `DEMO_MODE=true`
2. The site will work with sample data (no user registration/database)
3. Perfect for showcasing the website functionality

## Need Help?

- **Render Documentation**: [render.com/docs](https://render.com/docs)
- **PostgreSQL on Render**: [render.com/docs/databases](https://render.com/docs/databases)
- **PHP on Render**: [render.com/docs/deploy-php](https://render.com/docs/deploy-php)

---

**Note**: Remember to commit and push any changes to GitHub before deploying, as Render deploys directly from your Git repository. 