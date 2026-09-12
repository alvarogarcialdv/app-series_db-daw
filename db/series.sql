CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    genero VARCHAR(100),
    UNIQUE KEY uq_series_titulo (titulo)
);

INSERT IGNORE INTO series (titulo, genero) VALUES
    ('Breaking Bad', 'Drama'),
    ('Stranger Things', 'Ciencia ficción'),
    ('The Office', 'Comedia'),
    ('Game of Thrones', 'Fantasía'),
    ('Sherlock', 'Misterio');
