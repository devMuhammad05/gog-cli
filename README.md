<p align="center">
    <img src="https://raw.githubusercontent.com/laravel-zero/docs/master/images/logo/laravel-zero-readme.png" alt="Logo" height="100">
</p>

# Gog CLI

**Gog CLI** is a powerful, interactive command-line interface for managing your Gmail, built with **Laravel Zero**. It allows you to check your emails directly from your terminal with a beautiful, modern UI.

![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel Zero](https://img.shields.io/badge/Laravel%20Zero-10.0+-FF2D20?style=flat-square&logo=laravel&logoColor=white)

## ✨ Features

- **📨 Interactive Email List**: View your recent emails in a beautifully formatted table.
- **👀 Quick Preview**: Select an email to instantly view its details and snippet.
- **🔐 Secure Authentication**: OAuth2 integration with Google for secure login.
- **⚡ Fast**: Built for speed and efficiency in the terminal.

## 🚀 Installation

1. **Clone the repository**

    ```bash
    git clone https://github.com/devMuhammad05/gog-cli.git
    cd gog-cli
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Setup Credentials**
    - Place your Google OAuth `credentials.json` file in the project's storage path (or configured path).
    - Ensure the `google.php` config points to the correct credentials location.

## 🎮 Usage

### Authentication

First, you need to authenticate with your Google account. Run:

```bash
php gog-cli auth:login
```

Follow the interactive prompts to authorize the application in your browser.

### List Emails

To view your emails, run:

```bash
php gog-cli gmail:list
```

This will:

1. Fetch your latest emails (with a cool spinner!).
2. Display them in a table (#, From, Subject, Date).
3. Prompt you to enter the `#` of the email you want to read.

### List Options

You can specify the number of emails to retrieve:

```bash
php gog-cli gmail:list --limit=20
```

## 🛠 Tech Stack

- **[Laravel Zero](https://laravel-zero.com/)** - The framework for console artisans.
- **[Laravel Prompts](https://laravel.com/docs/prompts)** - For beautiful and user-friendly forms.
- **[Google API Client](https://github.com/googleapis/google-api-php-client)** - Interaction with Gmail API.

## 📄 License

Gog CLI is open-sourced software licensed under the [MIT license](LICENSE).
