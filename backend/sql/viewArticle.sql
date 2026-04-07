-- VIEW --

CREATE VIEW data_article_view AS
SELECT
    a.id,
    a.title,
    a.content,
    a.created_at,
    a.is_published,
    u.pseudo,
    u.avatar
FROM article AS a
INNER JOIN `user` AS u ON a.author_id = u.id;