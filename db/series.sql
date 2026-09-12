CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    genero VARCHAR(100),
    UNIQUE KEY uq_series_titulo (titulo)
);

INSERT INTO series (titulo, genero)
SELECT datos.titulo, datos.genero
FROM (
    SELECT 'Breaking Bad' AS titulo, 'Drama' AS genero
    UNION ALL SELECT 'Stranger Things', 'Ciencia ficción'
    UNION ALL SELECT 'The Office', 'Comedia'
    UNION ALL SELECT 'Game of Thrones', 'Fantasía'
    UNION ALL SELECT 'Sherlock', 'Misterio'
) AS datos
WHERE NOT EXISTS (
    SELECT 1
    FROM series existentes
    WHERE existentes.titulo = datos.titulo
);
