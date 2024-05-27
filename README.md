// migrate
docker-compose exec pdf-signer php artisan migrate
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
composer dump-autoload

# Project Name

## Table of Contents
- [Project Name](#project-name)
  - [Table of Contents](#table-of-contents)
  - [Introduction](#introduction)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Project Structure](#project-structure)
  - [Running the Tests](#running-the-tests)
  - [Usage Scenarios](#usage-scenarios)
    - [Scenario 1: Login](#scenario-1-login)
    - [Scenario 2: PDF Upload](#scenario-2-pdf-upload)
    - [Scenario 3: PDF Signature from Dashboard](#scenario-3-pdf-signature-from-dashboard)
    - [Scenario 4: File Size and Format Validation](#scenario-4-file-size-and-format-validation)
  - [Deployment to Production](#deployment-to-production)
  - [Contributing](#contributing)
  - [License](#license)

## Introduction
Provide a brief introduction to your project, what it does, and its main features.

## Requirements
List all the dependencies and tools needed to run the project.
- Docker
- Docker Compose
- Node.js & NPM (for front-end assets)

## Installation
Follow these steps to set up the project locally.

1. Clone the repository:
    ```bash
    git clone https://github.com/yourusername/yourproject.git
    cd yourproject
    ```

2. Copy the example environment file:
    ```bash
    cp .env.example .env
    ```

3. Add your own credentails for AWS and Google. You can use the following Google envs values, but they can be deleted anytime
   ```env
    GOOGLE_CLIENT_ID=7409894092-1hddmrerg8q0usg49lri18u7jdefc7ns.apps.googleusercontent.com
    GOOGLE_CLIENT_SECRET=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
    GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

    AWS_ACCESS_KEY_ID=""
    AWS_SECRET_ACCESS_KEY=""
    AWS_DEFAULT_REGION=""
    AWS_BUCKET="e"
    AWS_USE_PATH_STYLE_ENDPOINT=false
    AWS_URL=""
   ```

4. Build and start the containers:
    ```bash
    docker-compose up -d --build
    ```

5. Run database migrations:
    ```bash
    docker-compose exec app php artisan migrate
    ```

6. Build and start the frontend:
    ```bash
    npm install
    npm run dev
    ```

7. Run database migrations:
    ```bash
    php artisan migrate
    ```

8. Access the application:
    Open your browser and navigate to `http://localhost:8000`.

## Project Structure
Provide an overview of the project's structure, highlighting key directories and files.

```
app/
|-- Application/
|   |-- Document/
|       |-- DocumentService.php
|-- Domain/
|   |-- Document/
|       |-- Document.php
        |-- DocumentRepository.php
|-- Infrastructure/
    |-- Console/
    |-- Exceptions/
    |-- Http/
    |-- Persistence/
        |-- DocumentRepositoryImpl.php
    |-- Policies/
    |-- Providers/
    |-- Services/
        |-- AwsS3Service.php
        |-- EIDEasyService.php
|-- Presentation/
    |-- Auth/
        |-- AuthController.php
        |-- SocialiteController.php
|-- bootstrap/
|-- config/
|-- database/
|-- resources/
|-- routes/
|-- tests/
    |-- Feature/
        |-- DocumentControllerTest.php
    |-- Unit/
        |-- Application/
            |-- Document/
                |-- DocumentServiceTest.php
        |-- Infrastructure/
            |-- Persistence/
                |-- DocumentRepositoryImplTest.php
```


## Running the Tests

1. Run the PHPUnit tests:
    ```bash
    php artisan test
    ```

2. Run any other relevant tests or linters:
    ```bash
    npm run lint
    ```

## Usage Scenarios
### Scenario 1: Login

**Description:** The user logs into the platform using Google authentication.

**Steps:**
1. Access the homepage by navigating to `http://localhost:8000/`.
2. Click on "Login with Google".
3. You will be redirected to the Gmail login page to authenticate.
4. After logging in, you will be redirected to the `/dashboard`.

**Expected Results:**
- The user is authenticated via Google.
- The user is redirected to the dashboard page.
- The session is created for the user.

### Scenario 2: PDF Upload

**Description:** The user uploads a PDF file for signature.

**Steps:**
1. Drag and drop the PDF file into the upload box or click on the upload box to select the file.
2. Follow the signature flow.
3. Upon completing the signature process, you will be redirected to the `/dashboard`.
4. In the dashboard, you can download the original PDF, the signed PDF, or delete the record.
5. The dashboard will display a list of all registered PDFs.

**Expected Results:**
- The PDF file is uploaded and processed.
- The user is directed through the signature flow.
- The signed PDF is saved, and the user is redirected to the dashboard.
- The user can download or delete the PDF from the dashboard.
- The dashboard displays all uploaded PDFs.

### Scenario 3: PDF Signature from Dashboard

**Description:** The user completes the signature process for a PDF that was not fully signed during the upload process.

**Steps:**
1. Log in and navigate to the `/dashboard`.
2. Identify the PDF files that have not been signed (these will have a button to complete the signature).
3. Click the button to complete the signature process.
4. Follow the remaining steps to complete the signature.

**Expected Results:**
- The user can complete the signature process for any PDF that was partially processed.
- The signed PDF is saved, and the dashboard is updated.

### Scenario 4: File Size and Format Validation

**Description:** The system validates the file size and format during the upload process.

**Steps:**
1. Attempt to upload a file that exceeds the allowed size or is in a format other than PDF.
2. Observe the alert messages displayed on the screen.

**Expected Results:**
- The system validates the file size and format.
- If the file exceeds the allowed size or is not a PDF, an alert message is displayed informing the user of the issue.


## Deployment to Production
Explain the steps to deploy the application to a production environment.

1. Set up your production environment.
2. Clone the repository on the production server.
3. Install dependencies:
    ```bash
    composer install --optimize-autoloader --no-dev
    npm install --production
    npm run build
    ```

4. Set up the environment file:
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. Configure the `.env` file with production database credentials.
6. Run database migrations:
    ```bash
    php artisan migrate --force
    ```

7. Set up the correct permissions:
    ```bash
    chown -R www-data:www-data storage
    chown -R www-data:www-data bootstrap/cache
    ```

8. Configure the web server (e.g., Nginx or Apache).
9. Restart the web server.

## Contributing
Provide guidelines for contributing to the project, such as how to submit pull requests or report issues.

## License
Specify the license under which the project is distributed.

```markdown
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.