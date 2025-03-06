# AlgoTrig Trading Application

A PHP-based algorithmic trading application that integrates with the Zerodha trading platform to manage and execute trading strategies.

## Features

- Real-time position tracking
- Automated order execution
- Portfolio rebalancing
- Responsive web interface
- Secure authentication
- Configurable refresh intervals

## Requirements

- PHP 7.4 or higher
- Composer
- Zerodha trading account
- Web server (Apache/Nginx)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/yourusername/algotrig.git
cd algotrig
```

2. Install dependencies:
```bash
composer install
```

3. Configure environment variables:
Create a `.env` file in the root directory with the following variables:
```env
APP_DEBUG=false
ZERODHA_API_KEY=your_api_key
ZERODHA_SECRET=your_secret_key
```

4. Configure your web server:
- Point the document root to the `public` directory
- Ensure the `logs` directory is writable
- Enable URL rewriting (mod_rewrite for Apache)

## Directory Structure

```
algotrig/
├── assets/
│   ├── css/
│   └── js/
├── config/
│   └── config.php
├── logs/
├── public/
│   └── index.php
│   └── login.php
│   └── logout.php
├── src/
│   └── config.php
│   └── functions.php
├── templates/
│   └── index.php
├── vendor/
├── .gitignore
├── composer.json
└── README.md
```

## Usage

1. Access the application through your web browser
2. Log in with your Zerodha credentials
3. Configure your trading parameters
4. Monitor positions and execute orders

## Security Considerations

- All API keys are stored in environment variables
- Session security is enforced with secure cookie settings
- Input validation and sanitization are implemented
- CSRF protection is in place
- Error messages are logged but not displayed to users in production

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