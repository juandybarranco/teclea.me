INSERT INTO user (username, password, email, sign_up_date, country, is_Public, is_Block, is_Suspended, is_Admin, visits) 
VALUES ('Admin', '$2y$06$K5hm8cnHe3XSSEsKZwUJSOs.QH4IylBCXPkqa0ogsh6aZg9dcDrH2', 'admin@admin.com', NOW(), 'España', true, false, false, true, 0);

INSERT INTO community (id, name, description, creation_date, privacy, is_block, is_suspended, is_deleted, visits)
VALUES (1, 'General', 'Comunidad General', NOW(), 'default', false, false, false, 0);