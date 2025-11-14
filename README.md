# ShopNets E-commerce Platform

Modern PHP e-commerce platform with admin panel and user interface.

## Features

- User registration and authentication
- Product catalog with categories
- Shopping cart functionality
- Order management
- Admin panel for managing products, categories, orders, and users
- Responsive design
- File upload support

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

## Local Development Setup

1. Clone the repository:
```bash
git clone <your-repo-url>
cd ShopNets
```

2. Install dependencies:
```bash
composer install
```

3. Copy environment file:
```bash
cp .env.example .env
```

4. Update `.env` file with your database credentials

5. Set up database:
```bash
php scripts/setup.php
```

6. Start local server:
```bash
php -S localhost:8000
```

## Deployment

### Deploy to Render

1. Connect your GitHub repository to Render
2. Create a new Web Service
3. Set build command: `composer install --no-dev --optimize-autoloader && php scripts/setup.php`
4. Set start command: `php -S 0.0.0.0:$PORT -t .`
5. Add environment variables:
   - `DATABASE_URL`: Your MySQL database URL
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`

### Deploy to Railway

1. Connect your GitHub repository to Railway
2. Add MySQL database service
3. Set environment variables:
   - `DATABASE_URL`: Will be auto-populated from MySQL service
   - `APP_ENV`: `production`
   - `APP_DEBUG`: `false`

### Environment Variables

Required environment variables for production:

- `DATABASE_URL`: MySQL connection string
- `APP_ENV`: Set to `production`
- `APP_DEBUG`: Set to `false`
- `APP_URL`: Your application URL

## Default Admin Account

- **Username**: admin
- **Email**: admin@shopnets.com
- **Password**: admin123

⚠️ **Important**: Change the default admin password after first login!

## File Structure

```
ShopNets/
├── admin/              # Admin panel
├── user/               # User interface
├── database/           # Database schema
├── scripts/            # Setup scripts
├── config.php          # Main configuration
├── composer.json       # PHP dependencies
├── render.yaml         # Render deployment config
├── railway.json        # Railway deployment config
└── Dockerfile          # Docker configuration
```

## Database Schema

The application automatically creates the following tables:
- `users` - User accounts
- `categories` - Product categories
- `products` - Products
- `orders` - Customer orders
- `order_items` - Order line items
- `cart` - Shopping cart
- `settings` - Application settings

## Upload Directories

Make sure these directories are writable:
- `admin/assets/images/uploads/`
- `user/assets/images/uploads/`

## Security Notes

- Change default admin credentials
- Set proper file permissions
- Use HTTPS in production
- Keep environment files secure
- Regular security updates

## Support

For issues and questions, please open an issue in the repository.

## License

This project is licensed under the MIT License.