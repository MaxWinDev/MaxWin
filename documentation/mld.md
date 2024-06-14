``` mermaid
erDiagram
    USER {
        bigint id_utilisateur PK
        decimal balance
        string username
        string password
        string email
        boolean isVerified
    }

    TRANSACTIONS {
        int id PK
        string type
        decimal amount
        string currency
        datetime date
        bigint user_id FK
    }

    WINS {
        int id PK
        decimal bet
        decimal amount
        string machineName
        datetime date
        bigint user_id FK
    }

    USER ||--o{ TRANSACTIONS : "has"
    USER ||--o{ WINS : "has"
```
