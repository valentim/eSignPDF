

# ESignPDF

## Table of Contents
- [ESignPDF](#esignpdf)
  - [Table of Contents](#table-of-contents)
  - [Introduction](#introduction)
  - [Requirements](#requirements)
  - [Installation](#installation)
  - [Running the Tests](#running-the-tests)
  - [Project Structure](#project-structure)
  - [Usage Scenarios](#usage-scenarios)
    - [Scenario 1: Login](#scenario-1-login)
    - [Scenario 2: PDF Upload](#scenario-2-pdf-upload)
    - [Scenario 3: PDF Signature from Dashboard](#scenario-3-pdf-signature-from-dashboard)
    - [Scenario 4: File Size and Format Validation](#scenario-4-file-size-and-format-validation)
  - [Local Development](#local-development)
  - [Deployment to Production](#deployment-to-production)

## Introduction
The project is an e-signature solution called ESignPDF. It allows users to upload PDF files for signature and provides a dashboard to manage the signed documents.

- Demo version can be view here: [Demo](http://eideasy.xyz) 
- API Documentation [here](http://eideasy.xyz/api/documentation)

Below are the key features implemented in this project:

Key Features
1. Google Authentication with Socialite and Sanctum
I have implemented Google authentication using Socialite and Sanctum, providing a seamless and secure login process. This integration simplifies user authentication while ensuring secure access to private documents.

2. Secure File Storage on S3
To maintain the security and integrity of our files, all documents are stored in Amazon S3. Access to these files is controlled through temporary signed URLs, adding an extra layer of security and ensuring that only authorized users can retrieve the documents.

3. Modern Frontend with Vite and React
The frontend is built using Vite and React, leveraging the latest in web development technologies for fast, efficient, and dynamic user interfaces. This setup provides a robust and responsive user experience.

4. - Added CI/CD pipeline for automated deployment to AWS ECS.


## Requirements
List all the dependencies and tools needed to run the project.
- Docker
- Docker Compose
- Node.js (version 20) & NPM (for front-end assets)

## Installation
Follow these steps to run the project locally.

1. Clone the repository:
    ```bash
    git clone https://github.com/valentim/eSignPDF.git
    cd eSignPDF
    ```

2. Build and start the containers:
    ```bash
    docker-compose up -d
    ```

3. Access the application:
    Open your browser and navigate to `http://localhost`.

## Running the Tests

1. Run the PHPUnit tests:
    ```bash
    php artisan test
    ```

2. You should see something similar to this image:
<p align="center">
  <img src="./tests.png" alt="Resultados dos Testes">
</p>


## Project Structure
This project structure follows a layered architecture approach, which separates concerns and organizes code into distinct layers. Each layer has a specific responsibility, making the codebase easier to maintain, test, and scale. The key layers include:

Application Layer: Contains business logic and services, encapsulating use cases and processes.
Domain Layer: Defines the core domain models and repository interfaces, representing the business entities and their interactions.
Infrastructure Layer: Manages external services, implementations of repositories, and other system-level concerns, ensuring separation from the domain logic.
Presentation Layer: Handles the user interface and interaction, managing controllers and authentication.
This organization promotes a clean separation of concerns, modularity, and easier testing, enabling a more scalable and maintainable codebase.

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

## Local Development
Follow these steps to set up the project locally.

1. Clone the repository:
    ```bash
    git clone https://github.com/valentim/eSignPDF.git
    cd eSignPDF
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
    docker-compose -f docker-compose.development.yaml up -d --build
    ```

5. Run database migrations:
    ```bash
    php artisan migrate
    ```

    If you are running MySQL inside docker, then you need to run inside the pdf-signer container:
    ```bash
    docker-compose -f docker-compose.development.yaml exec pdf-signer php artisan test
    ```



6. Build and start the frontend:
    ```bash
    npm install
    npm run dev
    ```

7. Access the application:
    Open your browser and navigate to `http://localhost:8000`.

## Deployment to Production
The deployment process is automated using GitHub Actions triggered by a push to the main branch.
GitHub Actions workflow (deploy.yaml) will perform the following steps:

1. Build the Docker image of the project.
2. Clone the repository on the production server.
3. Publish the Docker image to Docker Hub.
4. Deploy the application to Amazon ECS using Fargate via ecs-cli commands.:
