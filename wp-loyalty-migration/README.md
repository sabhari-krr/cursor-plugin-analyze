# WPLoyalty Migration Plugin

A WordPress plugin for migrating loyalty program data from various sources to WPLoyalty, built using modern architectural patterns inspired by the wprelay plugin.

## 🏗️ Architecture Overview

This plugin follows a **Container-Based MVC (Model-View-Controller) architecture** with a sophisticated routing system, providing:

- **Clean separation of concerns**
- **Easy extensibility for Pro features**
- **Configuration-driven approach**
- **Centralized hook management**
- **Service container pattern**

## 📁 Directory Structure

```
wp-loyalty-migration/
├── app/                           # Core application logic
│   ├── App.php                   # Application bootstrap
│   ├── Container.php             # Dependency injection container
│   ├── Route.php                 # AJAX routing system
│   ├── Setup.php                 # Plugin lifecycle management
│   ├── Model.php                 # Base model class
│   ├── Hooks/                    # Hook registration classes
│   │   ├── RegisterHooks.php     # Base hook registration
│   │   ├── AdminHooks.php        # Admin-specific hooks
│   │   ├── AssetsActions.php     # Asset management
│   │   ├── WooCommerceHooks.php # WooCommerce integration
│   │   ├── CustomHooks.php       # Custom plugin hooks
│   │   ├── WPHooks.php           # WordPress core hooks
│   │   └── ConditionalHooks.php  # Conditional hook logic
│   ├── Helpers/                  # Helper utility classes
│   │   ├── PluginHelper.php      # Plugin-specific helpers
│   │   ├── WordpressHelper.php   # WordPress core helpers
│   │   └── Functions.php         # Migration-specific functions
│   └── config/                   # Configuration files
│       └── settings.php          # Plugin settings configuration
├── Core/                          # Core plugin functionality
│   ├── Controllers/              # API and admin controllers
│   │   ├── Admin/                # Admin page controllers
│   │   │   └── PageController.php
│   │   ├── Api/                  # API endpoint controllers
│   │   │   ├── MigrationController.php
│   │   │   ├── DashboardController.php
│   │   │   └── SettingsController.php
│   │   └── LocalDataController.php
│   ├── Models/                   # Data models
│   │   ├── MigrationModel.php    # Migration data model
│   │   ├── MigrationLogModel.php # Migration logs model
│   │   └── MigrationSourceModel.php # Migration sources model
│   └── routes/                   # Route definitions
│       ├── admin-hooks.php       # Admin hook routes
│       ├── auth-api.php          # Authenticated API routes
│       ├── guest-api.php         # Guest API routes
│       ├── woocommerce-hooks.php # WooCommerce hook routes
│       ├── custom-hooks.php      # Custom hook routes
│       └── wp-hooks.php          # WordPress hook routes
├── Pro/                           # Premium features (future extensibility)
│   ├── Controllers/              # Pro controllers
│   ├── Models/                   # Pro models
│   ├── Resources/                # Pro resources
│   ├── ShortCodes/               # Pro shortcodes
│   ├── ValidationRequest/        # Pro validation
│   ├── routes/                   # Pro route definitions
│   └── views/                    # Pro view templates
├── resources/                     # Frontend assets
│   ├── admin/                    # Admin-specific assets
│   ├── css/                      # Stylesheets
│   ├── emails/                    # Email templates
│   ├── pages/                     # Page templates
│   ├── scripts/                   # JavaScript files
│   └── templates/                 # PHP templates
├── i18n/                         # Internationalization
│   └── languages/                # Language files
├── vendor/                        # Composer dependencies
├── composer.json                  # Composer configuration
├── wp-loyalty-migration.php      # Main plugin file
└── README.md                      # This file
```

## 🔧 Key Design Patterns

### 1. Container Pattern
The plugin uses a simple dependency injection container for service management:

```php
$app = wlmr_app();
$app->bind('migration_service', new MigrationService());
$service = $app->make('migration_service');
```

### 2. Route Configuration
Declarative route definitions in PHP files instead of scattered WordPress hooks:

```php
// routes/auth-api.php
return [
    'start_migration' => [
        'callable' => [MigrationController::class, 'start'],
        'middleware' => ['auth', 'nonce']
    ]
];
```

### 3. Hook Abstraction
Centralized hook registration through dedicated classes:

```php
class AdminHooks extends RegisterHooks
{
    public static function register()
    {
        static::registerCoreHooks('admin-hooks.php');
        if (PluginHelper::isPRO()) {
            static::registerProHooks('admin-hooks.php');
        }
    }
}
```

### 4. Pro/Free Separation
Clean separation between core and premium features:

```php
// Check if Pro features are available
if (wlmr_app()->get('is_pro_plugin')) {
    // Load Pro routes and features
    $handlers = array_merge($handlers, require(PluginHelper::pluginRoutePath(true) . '/auth-api.php'));
}
```

## 🚀 Getting Started

### 1. Installation
1. Upload the plugin to `/wp-content/plugins/wp-loyalty-migration/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to 'WPLoyalty Migration' in the admin menu

### 2. Basic Usage
The plugin provides a dashboard interface for:
- Viewing migration status
- Starting new migrations
- Monitoring progress
- Viewing migration history
- Managing settings

### 3. Configuration
Settings can be configured through:
- Admin interface
- Configuration files in `app/config/`
- WordPress options API

## 🔌 Extending the Plugin

### Adding New Migration Sources
1. Create a new source class in `Core/Models/`
2. Add the source to the configuration in `app/config/settings.php`
3. Implement the required methods

### Adding Pro Features
1. Place Pro-specific code in the `Pro/` directory
2. Add routes to `Pro/routes/`
3. Use the `is_pro_plugin` flag to conditionally load features

### Custom Hooks
The plugin provides several action and filter hooks:

```php
// Before migration starts
do_action('wlmr_before_migration', $migration_id, $source_type, $settings);

// After migration completes
do_action('wlmr_after_migration', $migration_id, $status);

// Migration progress updates
do_action('wlmr_migration_progress', $progress);
```

## 🧪 Development

### Requirements
- PHP 7.3+
- WordPress 5.9+
- WooCommerce 7.0+

### Development Setup
1. Clone the repository
2. Run `composer install` to install dependencies
3. Activate the plugin in WordPress
4. Use the development tools in the admin interface

### Testing
The plugin includes built-in health checks and testing endpoints:
- Database connectivity
- File system access
- Memory usage monitoring
- API endpoint testing

## 📚 API Reference

### AJAX Endpoints
All AJAX endpoints are prefixed with `wlmr_migration` and require proper nonce verification.

### Core Endpoints
- `start_migration` - Start a new migration
- `stop_migration` - Stop an active migration
- `get_migration_status` - Get current migration status
- `get_migration_progress` - Get migration progress
- `get_migration_logs` - Get migration logs

### Settings Endpoints
- `get_migration_settings` - Get migration settings
- `save_migration_settings` - Save migration settings
- `get_plugin_settings` - Get plugin settings
- `save_plugin_settings` - Save plugin settings

## 🔒 Security Features

- Nonce verification for all AJAX requests
- User capability checks
- Input sanitization and validation
- SQL injection prevention
- XSS protection

## 🌟 Benefits of This Architecture

1. **Maintainability**: Clear separation of concerns
2. **Scalability**: Easy to add new features and modules
3. **Testability**: Services can be easily unit tested
4. **Extensibility**: Pro features cleanly separated
5. **Flexibility**: Configuration-driven approach
6. **Readability**: Consistent patterns throughout

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## 📄 License

This plugin is licensed under the GPL v3 or later.

## 🆘 Support

For support and questions:
- Check the plugin documentation
- Review the code comments
- Contact the development team

---

**Note**: This plugin is designed to be easily extensible for future Pro features while maintaining a clean, maintainable codebase following modern WordPress development practices.