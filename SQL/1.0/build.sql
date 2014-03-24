BEGIN;

CREATE TABLE user_type (
    user_type_id    INT(10)         NOT NULL auto_increment,
    name            VARCHAR(255)    NOT NULL UNIQUE,
    PRIMARY KEY (user_type_id)
);

INSERT INTO user_type (name) VALUES ('admin'), ('employee');

CREATE TABLE users (
    user_id         INT(10)         NOT NULL auto_increment,
    first_name      VARCHAR(255)    NOT NULL,
    last_name       VARCHAR(255)    NOT NULL,
    email           VARCHAR(255)    NOT NULL UNIQUE,
    password        VARCHAR(32)     NOT NULL,
    user_type_id    INT(10)         NOT NULL,
    active          BOOLEAN         NOT NULL,
    PRIMARY KEY (user_id),
    FOREIGN KEY (user_type_id) REFERENCES user_type(user_type_id)
);

CREATE TABLE resource (
    resource_id     INT(10)         NOT NULL auto_increment,
    name            VARCHAR(255)    NOT NULL UNIQUE,
    PRIMARY KEY (resource_id)
);

INSERT INTO resource
    (name)
VALUES
    ('default:index:index')
,   ('default:users:index')
,   ('default:users:view')
,   ('default:users:edit')
,   ('default:users:get-user')
,   ('default:customer:index')
,   ('default:customer:view')
,   ('default:customer:edit')
,   ('default:customer:get-customer')
,   ('default:batch:index')
,   ('default:batch:view')
,   ('default:batch:edit')
,   ('default:batch:get-batch')
,   ('default:recipient:index')
,   ('default:recipient:upload')
,   ('default:recipient:view')
,   ('default:recipient:edit')
,   ('default:recipient:get-recipient')
;

CREATE TABLE user_type_resource (
    user_type_resource_id   INT(10)     NOT NULL auto_increment,
    resource_id             INT(10)     NOT NULL,
    user_type_id            INT(10)     NOT NULL,
    PRIMARY KEY (user_type_resource_id),
    FOREIGN KEY (user_type_id) REFERENCES user_type(user_type_id),
    FOREIGN KEY (resource_id) REFERENCES resource(resource_id),
    CONSTRAINT uc_user_type_id_resource_id UNIQUE (resource_id, user_type_id)
);

INSERT INTO user_type_resource
    (resource_id, user_type_id)
VALUES
    (1,1),(1,2), -- default:index:index
    (2,1), -- default:users:index
    (3,1), -- default:users:view
    (4,1), -- default:users:edit
    (5,1), -- default:users:get-user
    (6,1), -- default:customer:index
    (7,1), -- default:customer:view
    (8,1), -- default:customer:edit
    (9,1), -- default:customer:get-customer
    (10,1), -- default:batch:index
    (11,1), -- default:batch:view
    (12,1), -- default:batch:edit
    (13,1), -- default:batch:get-batch
    (14,1), -- default:recipient:index
    (15,1), -- default:recipient:upload
    (16,1), -- default:recipient:view
    (17,1), -- default:recipient:edit
    (18,1) -- default:recipient:get
;

CREATE TABLE customer (
    customer_id     INT(10)         NOT NULL auto_increment,
    name            VARCHAR(255)    NOT NULL UNIQUE,
    active          BOOLEAN         NOT NULL,
    PRIMARY KEY (customer_id)
);

CREATE TABLE batch (
    batch_id        INT(10)         NOT NULL auto_increment,
    customer_id     INT(10)         NOT NULL,
    insert_ts       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    name            VARCHAR(255)    NOT NULL,
    contact_name    VARCHAR(255)    NULL,
    contact_phone   VARCHAR(255)    NULL,
    contact_email   VARCHAR(255)    NULL,
    street          VARCHAR(255)    NULL,
    suite_apt       VARCHAR(255)    NULL,
    city            VARCHAR(255)    NULL,
    state           VARCHAR(255)    NULL,
    postal_code     VARCHAR(255)    NULL,
    active          BOOLEAN         NOT NULL,
    PRIMARY KEY (batch_id),
    FOREIGN KEY (customer_id) REFERENCES customer(customer_id),
    CONSTRAINT uc_customer_name UNIQUE (name, customer_id)
);

CREATE TABLE recipient (
    recipient_id        INT(10)         NOT NULL auto_increment,
    batch_id            INT(10)         NOT NULL,
    email               VARCHAR(255)    NULL,
    first_name          VARCHAR(255)    NOT NULL,
    last_name           VARCHAR(255)    NOT NULL,
    address_line_one    VARCHAR(255)    NOT NULL,
    address_line_two    VARCHAR(255)    NULL,
    city                VARCHAR(255)    NOT NULL,
    state               VARCHAR(255)    NOT NULL,
    postal_code         VARCHAR(255)    NOT NULL,
    verified_address    BOOLEAN         NOT NULL DEFAULT false,
    insert_ts           TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ship_ts             TIMESTAMP       NULL,
    tracking_number     VARCHAR(255)    NULL,
    shirt_sex           VARCHAR(255)    NOT NULL,
    shirt_size          VARCHAR(255)    NOT NULL,
    shirt_type          VARCHAR(255)    NOT NULL,
    quantity            INT(10)         NOT NULL,
    PRIMARY KEY (recipient_id),
    FOREIGN KEY (batch_id) REFERENCES batch(batch_id)
);

COMMIT;