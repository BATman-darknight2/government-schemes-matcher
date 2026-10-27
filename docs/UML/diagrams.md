# SchemePath UML diagrams

## 1. Use-case diagram

```mermaid
flowchart LR
  Visitor((Visitor)) --> Browse[Browse and search schemes]
  Visitor --> View[View scheme details]
  User((Registered user)) --> Register[Register / sign in]
  User --> Profile[Maintain profile]
  User --> Check[Run preliminary assessment]
  User --> Save[Save bookmarks]
  User --> History[Review own history]
  Admin((Administrator)) --> Catalogue[Manage schemes, status and sources]
  Admin --> Category[Manage categories]
  Admin --> Rules[Manage rule groups and conditions]
  Admin --> Docs[Manage required documents]
  Admin --> Audit[Review audit log]
  Check --> Explain[Review rule outcomes and missing data]
```

## 2. Class diagram

```mermaid
classDiagram
  class User {+id +name +email +role}
  class UserProfile {+date_of_birth +annual_income +state +occupation +residence_type}
  class Scheme {+id +title +status +verification_status +rule_version}
  class Category {+id +name +slug}
  class RuleGroup {+id +group_key +condition_operator}
  class EligibilityRule {+attribute_key +operator +expected_value +mandatory}
  class SchemeDocument {+name +description +mandatory}
  class SchemeSource {+source_url +verification_status +verified_at}
  class Assessment {+assessed_at +overall_status +rule_version +profile_snapshot}
  class AssessmentResult {+condition_description +result_status +explanation}
  class Bookmark {+created_at}
  class EligibilityEngine {+evaluate(profile,groups,join)}
  User "1" --> "0..1" UserProfile
  User "1" --> "0..*" Assessment
  User "1" --> "0..*" Bookmark
  Scheme "*" --> "*" Category
  Scheme "1" --> "0..*" RuleGroup
  RuleGroup "1" --> "0..*" EligibilityRule
  Scheme "1" --> "0..*" SchemeDocument
  Scheme "1" --> "0..*" SchemeSource
  Scheme "1" --> "0..*" Assessment
  Assessment "1" --> "0..*" AssessmentResult
  EligibilityEngine ..> EligibilityRule
```

## 3. Eligibility assessment activity

```mermaid
flowchart TD
  A([Start]) --> B[Authenticate user]
  B --> C[Load active scheme, groups and rules]
  C --> D[Load saved profile]
  D --> E{Rules valid?}
  E -- No --> F[Unable to assess]
  E -- Yes --> G[Evaluate applicability and profile value]
  G --> H{Value present?}
  H -- No --> I[UNKNOWN]
  H -- Yes --> J{Condition satisfied?}
  J -- Yes --> K[PASS]
  J -- No --> L[FAIL]
  I --> M[Combine required conditions and groups]
  K --> M
  L --> M
  F --> N[Persist snapshot, rule version and explanations]
  M --> N --> O[Display preliminary result] --> P([End])
```

## 4. Eligibility check sequence

```mermaid
sequenceDiagram
  actor User
  participant Browser
  participant PHP as PHP controller
  participant DB as MySQL
  participant Engine as EligibilityEngine
  User->>Browser: Submit check form + CSRF token
  Browser->>PHP: POST assessment
  PHP->>PHP: Verify session, role and CSRF
  PHP->>DB: Load active scheme, rules, profile
  DB-->>PHP: Profile snapshot inputs and rule version
  PHP->>Engine: evaluate(profile, groups, group join)
  Engine-->>PHP: Overall state and condition explanations
  PHP->>DB: Transaction: save assessment + result rows
  DB-->>PHP: Assessment ID
  PHP-->>Browser: Redirect to owned result page
  Browser-->>User: Show outcomes and disclaimer
```

## 5. Assessment lifecycle state

```mermaid
stateDiagram-v2
  [*] --> Submitted
  Submitted --> RulesLoaded
  RulesLoaded --> Evaluated
  RulesLoaded --> UnableToAssess: invalid or absent rules
  Evaluated --> PotentiallyEligible: all required conditions pass
  Evaluated --> MoreInformation: required value is unknown
  Evaluated --> ConditionsNotMet: combined required conditions fail
  PotentiallyEligible --> Persisted
  MoreInformation --> Persisted
  ConditionsNotMet --> Persisted
  UnableToAssess --> Persisted
  Persisted --> Reviewed
  Reviewed --> [*]
```

## 6. Communication diagram

```mermaid
flowchart LR
  U[User] -->|1 submit check| B[Browser form]
  B -->|2 POST + CSRF| C[PHP controller]
  C -->|3 load| D[(PDO / MySQL)]
  C -->|4 evaluate| E[EligibilityEngine]
  E -->|5 status and per-rule results| C
  C -->|6 transactional save| D
  C -->|7 redirect with assessment ID| B
  B -->|8 display owned result| U
```

## 7. Component diagram

```mermaid
flowchart TB
  Browser[Browser: HTML, CSS, JavaScript]
  Router[PHP front controller]
  Auth[Authentication and authorization helpers]
  Profile[Profile workflow]
  Catalogue[Scheme catalogue and admin management]
  Engine[Eligibility engine service]
  Views[PHP view templates]
  PDO[PDO data access]
  MySQL[(MySQL / MariaDB)]
  Browser <--> Router
  Router --> Auth
  Router --> Profile
  Router --> Catalogue
  Router --> Engine
  Router --> Views
  Auth --> PDO
  Profile --> PDO
  Catalogue --> PDO
  Engine --> PDO
  PDO --> MySQL
```

## 8. Deployment diagram

```mermaid
flowchart LR
  subgraph PC[One Windows development computer]
    Browser[Modern browser]
    Apache[Apache HTTP server]
    PHP[PHP application in XAMPP]
    MySQL[(MySQL or MariaDB server)]
    Browser <-->|HTTP localhost| Apache
    Apache --> PHP
    PHP -->|PDO MySQL| MySQL
  end
```

In the documented XAMPP setup, browser, Apache/PHP, and MySQL are separate logical nodes on the same physical computer.
