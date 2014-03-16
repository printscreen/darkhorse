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
    (9,1) -- default:customer:get-customer
;

CREATE TABLE customer (
    customer_id     INT(10)         NOT NULL auto_increment,
    name            VARCHAR(255)    NOT NULL UNIQUE,
    active          BOOLEAN         NOT NULL,
    PRIMARY KEY (customer_id)
);

COMMIT;