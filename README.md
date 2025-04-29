# AlgoTrig Trading Application

Web Development repository for the AlgoTrig UI in PHP. Contains experimental features and connects to the core backend at [algotrig/algotrig-php-core](https://github.com/algotrig/algotrig-php-core).

## Features

- Real-time position tracking
- Automated order execution
- Portfolio rebalancing
- Responsive web interface
- Secure authentication
- Configurable refresh intervals

## Requirements

- PHP 8.0 or higher
- Composer
- Zerodha trading account
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/algotrig/algotrig-web-ui-php.git
cd algotrig
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment variables:
Set the api_key and secret from [kite.trade](https://kite.trade) in the `algotrig.ini` file:
```env
api_key = yourapikey
secret = yourapisecretfromkite
```

4. Configure your web server:
- Point the document root to the `public` directory
- Enable URL rewriting (mod_rewrite for Apache)

## Directory Structure

```
algotrig/
├── assets/
│   ├── css/
│   └── js/
├── public/
│   └── index.php
│   └── login.php
│   └── logout.php
├── src/
│   └── config_loader.php
│   └── functions.php
├── templates/
│   └── index.php
├── vendor/
├── .gitignore
├── .htaccess
├── algotrig.ini
├── composer.json
├── composer.lock
├── LICENSE
└── README.md
```

## Usage

1. Access the application through your web browser
2. Log in with your Zerodha credentials
3. Monitor positions and execute orders

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Disclaimer

This software is for educational purposes only. Use at your own risk. The authors are not responsible for any financial losses incurred through the use of this software. 