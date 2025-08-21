CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    employee_code CHAR(7) NULL UNIQUE,
    role ENUM('manager','employee') NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS user_managers (
    employee_id INT NOT NULL,
    manager_id INT NOT NULL,
    PRIMARY KEY (employee_id, manager_id),

    CONSTRAINT fk_um_employee FOREIGN KEY (employee_id)
    REFERENCES users(id) ON DELETE CASCADE,

    CONSTRAINT fk_um_manager FOREIGN KEY (manager_id)
    REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vacation_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    manager_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,

    CONSTRAINT fk_vr_employee FOREIGN KEY (employee_id)
    REFERENCES users(id) ON DELETE CASCADE,

    CONSTRAINT fk_vr_manager FOREIGN KEY (manager_id)
    REFERENCES users(id) ON DELETE RESTRICT,

    INDEX idx_vr_employee (employee_id),
    INDEX idx_vr_manager  (manager_id),
    INDEX idx_vr_status   (status)
    ) ENGINE=InnoDB;
