---
type: design
status: draft
tags:
  - erd
  - database
  - mermaid
---

# ERD

Dokumen ini berisi rancangan ERD konseptual. Diagram menggunakan Mermaid agar dapat dibaca di Obsidian dengan plugin Mermaid bawaan.

## ERD Konseptual

```mermaid
erDiagram
    CLASS_ROOMS ||--o{ USERS : has
    USERS ||--o{ MODULES : creates
    USERS ||--o{ ACTIVITY_ANSWERS : submits
    USERS ||--o{ ASSESSMENT_ATTEMPTS : attempts
    USERS ||--o{ PROJECTS : submits
    MODULES ||--o{ LEARNING_UNITS : contains
    MODULES ||--o{ ASSESSMENTS : has
    MODULES ||--o{ GLOSSARIES : has
    MODULES ||--o{ REFERENCES : has
    LEARNING_UNITS ||--o{ MATERIALS : has
    LEARNING_UNITS ||--o{ MEDIA : has
    LEARNING_UNITS ||--o{ ACTIVITIES : has
    LEARNING_UNITS ||--o{ DISCUSSIONS : has
    LEARNING_UNITS ||--o{ ASSESSMENTS : has
    ACTIVITIES ||--o{ ACTIVITY_ANSWERS : receives
    ASSESSMENTS ||--o{ QUESTIONS : contains
    ASSESSMENTS ||--o{ ASSESSMENT_ATTEMPTS : receives
    QUESTIONS ||--o{ QUESTION_KEYWORDS : has
    QUESTIONS ||--o{ RUBRICS : has
    QUESTIONS ||--o{ STUDENT_ANSWERS : receives
    ASSESSMENT_ATTEMPTS ||--o{ STUDENT_ANSWERS : contains
    DISCUSSIONS ||--o{ DISCUSSIONS : replies

    CLASS_ROOMS {
        bigint id PK
        string name
        string school_year
    }

    USERS {
        bigint id PK
        string name
        string email
        string password
        bigint class_id FK
    }

    MODULES {
        bigint id PK
        string title
        string subject
        string grade
        string semester
        text description
        string learning_model
        string status
        bigint created_by FK
    }

    LEARNING_UNITS {
        bigint id PK
        bigint module_id FK
        string title
        text description
        integer order_number
        text learning_objective
    }

    MATERIALS {
        bigint id PK
        bigint learning_unit_id FK
        string title
        longText content
        integer order_number
    }

    MEDIA {
        bigint id PK
        bigint learning_unit_id FK
        string media_type
        string title
        string file_path
        string url
        text caption
    }

    ACTIVITIES {
        bigint id PK
        bigint learning_unit_id FK
        string activity_type
        string title
        text instruction
        json answer_schema
        integer order_number
    }

    ACTIVITY_ANSWERS {
        bigint id PK
        bigint activity_id FK
        bigint student_id FK
        longText answer_text
        json answer_json
        string file_path
        decimal score
        text feedback
    }

    ASSESSMENTS {
        bigint id PK
        bigint module_id FK
        bigint learning_unit_id FK
        string title
        string assessment_type
        integer kktp
        integer max_attempt
        boolean is_published
    }

    QUESTIONS {
        bigint id PK
        bigint assessment_id FK
        string question_type
        longText question_text
        json options_json
        json correct_answer_json
        string cognitive_level
        string literacy_aspect
        decimal score_weight
        integer order_number
    }

    QUESTION_KEYWORDS {
        bigint id PK
        bigint question_id FK
        string keyword
        decimal weight
    }

    RUBRICS {
        bigint id PK
        bigint question_id FK
        string project_type
        string criterion
        decimal max_score
        text description
    }

    ASSESSMENT_ATTEMPTS {
        bigint id PK
        bigint assessment_id FK
        bigint student_id FK
        integer attempt_number
        decimal total_score
        string status
        timestamp started_at
        timestamp submitted_at
    }

    STUDENT_ANSWERS {
        bigint id PK
        bigint attempt_id FK
        bigint question_id FK
        json answer_json
        longText answer_text
        decimal score
        text feedback
        decimal similarity_score
        decimal keyword_score
        decimal rubric_score
    }

    PROGRESS {
        bigint id PK
        bigint student_id FK
        bigint module_id FK
        bigint learning_unit_id FK
        string status
        integer progress_percentage
        timestamp completed_at
    }

    DISCUSSIONS {
        bigint id PK
        bigint learning_unit_id FK
        bigint student_id FK
        text content
        bigint parent_id FK
    }

    PROJECTS {
        bigint id PK
        bigint student_id FK
        bigint module_id FK
        string project_title
        text problem
        text objective
        text tools_materials
        text procedure
        text collected_data
        text expected_result
        text conclusion
        string file_path
        decimal score
        text feedback
        string status
    }

    GLOSSARIES {
        bigint id PK
        bigint module_id FK
        string term
        text definition
    }

    REFERENCES {
        bigint id PK
        bigint module_id FK
        text reference_text
    }
```

## Catatan Normalisasi

- `questions.options_json` dipakai agar opsi bisa fleksibel untuk pilihan ganda, kompleks, dan menjodohkan.
- `questions.correct_answer_json` dipakai agar kunci jawaban bisa menyimpan struktur array/object.
- `student_answers` memisahkan skor akhir, keyword score, rubric score, dan similarity score agar scoring transparan.
- `rubrics` dibuat bisa nullable ke question agar dapat dipakai juga untuk proyek.

## Risiko Desain

- JSON memudahkan fleksibilitas, tetapi validasi harus kuat.
- Laporan bisa lambat jika query progress tidak dioptimalkan.
- Soft delete perlu dipertimbangkan untuk data modul dan asesmen agar riwayat murid tidak hilang.
