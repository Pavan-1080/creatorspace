# CreatorSpace — Entity Relationship Diagram

## Database Relationships

```mermaid
erDiagram
    USERS ||--o{ OTP_VERIFICATIONS : receives
    USERS o|--o{ PROJECTS : creates
    USERS o|--o{ ARTICLES : writes

    USERS {
        int id PK
        varchar name
        varchar email UK
        varchar password
        enum role
        datetime email_verified_at
        timestamp created_at
    }

    OTP_VERIFICATIONS {
        int id PK
        int user_id FK
        varchar otp_hash
        enum purpose
        datetime expires_at
        timestamp created_at
    }

    PROJECTS {
        int id PK
        varchar title
        varchar category
        text description
        varchar image_url
        varchar project_url
        int created_by FK
        timestamp created_at
    }

    ARTICLES {
        int id PK
        varchar title
        varchar category
        text excerpt
        longtext content
        boolean published
        int created_by FK
        timestamp created_at
        timestamp updated_at
    }

    CONTACT_MESSAGES {
        int id PK
        varchar name
        varchar email
        varchar subject
        text message
        timestamp created_at
    }
```

## Relationship Explanation

- **Users and OTP verifications:** One user can have multiple OTP verification records. OTP records are deleted when their associated user is deleted.
- **Users and projects:** A user can create multiple projects. If the creator account is deleted, the project remains and its `created_by` value becomes `NULL`.
- **Users and articles:** A user can create multiple articles. If the creator account is deleted, the article remains and its `created_by` value becomes `NULL`.
- **Contact messages:** Messages are stored independently and do not currently have a foreign-key relationship to users.

## Database Design Notes

- Primary keys uniquely identify records.
- The users table uses a unique constraint on email.
- OTP records store a hashed OTP and an expiration timestamp.
- Indexes support project and article category/title queries.
- Passwords and OTPs are stored as hashes rather than plaintext.
