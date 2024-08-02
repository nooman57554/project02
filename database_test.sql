


CREATE TABLE IF NOT EXISTS admins (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_username VARCHAR(255) NOT NULL UNIQUE,
    admin_password VARCHAR(255) NOT NULL
);



CREATE TABLE IF NOT EXISTS students (
    student_id INT PRIMARY KEY AUTO_INCREMENT,
    student_username VARCHAR(255) NOT NULL UNIQUE,
    student_password VARCHAR(255) NOT NULL
);


CREATE TABLE IF NOT EXISTS teachers (
    teacher_id INT PRIMARY KEY AUTO_INCREMENT,
    teacher_name VARCHAR(30) DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS subjects (
    subject_id VARCHAR(20) PRIMARY KEY,
    subject_name VARCHAR(255) NOT NULL UNIQUE,
    no_hours_per_week INT NOT NULL,
    Sem_id INT NOT NULL CHECK (Sem_id BETWEEN 1 AND 8)
);


CREATE TABLE IF NOT EXISTS Lab_subjects (
    lab_subject_id VARCHAR(20) PRIMARY KEY,
    lab_subject_name VARCHAR(255) NOT NULL UNIQUE,
    no_hours_per_week INT NOT NULL,
    Sem_id INT NOT NULL CHECK (Sem_id BETWEEN 1 AND 8)
);





CREATE TABLE IF NOT EXISTS classes (
    class_id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(255) NOT NULL,
    section VARCHAR(10) NOT NULL
);

CREATE TABLE lab_assistants (
    assistant_id INT PRIMARY KEY AUTO_INCREMENT,
    assistant_name VARCHAR(100) NOT NULL
);


CREATE TABLE IF NOT EXISTS schedule (
    schedule_id INT PRIMARY KEY AUTO_INCREMENT,
    day VARCHAR(20) NOT NULL,
    slot VARCHAR(20) NOT NULL,
    subject_id VARCHAR(20), -- Changed to VARCHAR(20)
    teacher_id INT,
    class_id INT,
    section VARCHAR(10),
    batch VARCHAR(10) NULL,
    assistant_id INT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (assistant_id) REFERENCES lab_assistants(assistant_id)
);

CREATE TABLE Section (
    SectionName VARCHAR(10) NOT NULL,
    Sem INT NOT NULL
);





