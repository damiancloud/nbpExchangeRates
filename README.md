# Task: NBP API Integration - Symfony 6

## Description
### 1. Establish Connection with NBP API in XML Format and Table C
The application is expected to establish a connection with the National Bank of Poland (NBP) API in XML format and retrieve data from table C.
### 2. Create a Controller Handling REST API Requests
#### a. Endpoints
The controller should handle the following endpoints:
- `GET /api/exchange-rates/{currency}/{startDate}/{endDate}`
  - Parameters:
    - `{currency}`: Currency of interest (one of three: Euro, US Dollar, Swiss Franc).
    - `{startDate}`: Start date in 'Y-m-d' format.
    - `{endDate}`: End date in 'Y-m-d' format.
  - Constraints:
    - The maximum date range allowed is 7 days.
  - Returned data:
    - Day, buying rate, selling rate, and differences in buying and selling prices.
    - Price difference is the difference between the buying/selling rate of the current day and the previous day.
#### b. Automated Tests
Prepare automated tests for the functions implemented in this task. Verify the correctness of handling different scenarios, such as valid queries, unsupported currencies, incorrect date ranges, etc.
### 3. Use PHPDoc for Documentation
Utilize PHPDoc for detailed comments on classes, methods, objects, etc. Ensure code clarity for other developers.
### 4. Controller Routing
Place controller routing in the attributes of the controller class. Ensure that routing is defined in one location.
### 5. Require Mandatory X-TOKEN-SYSTEM Header
Mandate the inclusion of the `X-TOKEN-SYSTEM` header in every request. The token should be stored in the application's environment variables. Its value can be any string, for example, "a" or a randomly generated string.
### 6. Endpoint Documentation in OpenAPI
Create documentation for endpoints in the OpenAPI format. You can use a tool such as the Nelmio Bundle to automatically generate documentation.

---

# Getting Started
To start the project, follow these steps:

## 1. Run the following command to build the Docker container and install dependencies:
```bash
make start
```

## 2. Start the Docker container:
```bash
make up
```

Access the container's console:

```bash
make console
```

---

# Run app

```plaintext
http://localhost:8000/
```
Run test
```bash
var/www# php bin/phpunit
```

---

# OpenAPI Documentation
```plaintext
http://localhost:8000/api/doc
```
