# Integration Project: FOSSBilling and WordPress

## Project Overview

This project is an integration between FOSSBilling and WordPress using RabbitMQ for data exchange between the two systems. The objective is to ensure both systems operate on-premise without using any cloud services.

## Prerequisites

Before you start, make sure you have the following installed:

- **Linux Server**: As the operating system for hosting the services (if not using VirtualBox).
- **Docker**: For containerizing the application.
- **Docker Compose**: To manage multi-container Docker applications.
- **Git**: For version control.

## Getting Started

1. **Clone the repository**:
    ```bash
    git clone git@github.com:Hamza-180/integration.git
    cd integration
    ```

2. **Set up the environment**:
    - If necessary, use Oracle VirtualBox to create a virtual machine with a Linux server.
    - Ensure Docker and Docker Compose are installed on your Linux server.

3. **Build and Start Docker Containers**:
    - Build the Docker containers using:
      ```bash
      docker-compose build
      ```
    - Start the containers:
      ```bash
      docker-compose up
      ```

4. **Accessing the UI**:
    - Once the Docker containers are up and running, you can access the WordPress UI to manage and sync customer data with FOSSBilling.

## Features

- **Client Overview**: View a list of all clients.
- **Client Management**: Add, edit, or delete client data.
- **Bi-directional Communication**: Data updates in FOSSBilling automatically reflect in WordPress, and vice versa, using RabbitMQ and API calls.

## Important Notes

- All communication between WordPress and FOSSBilling is handled via RabbitMQ.
- Only API calls are allowed for data transfer. Make sure to configure senders and receivers correctly.
- Ensure that your code is consistently pushed to the Git repository and follows best practices.

## Additional Features

-  Continuous integration: Set up automatic testing, building, and deployment using CI/CD pipelines.

## Documentation

For more detailed documentation, please refer to the [Confluence page](https://amghar1800.atlassian.net/wiki/external/MjJhMGYwOGI2MTlmNDExZWEwNTA2NDQ4NmJmODRhN2E).

## Acknowledgements

While I did not directly reuse any code from the first attempt, the experience and lessons learned from it greatly inspired and informed the development of this project.
