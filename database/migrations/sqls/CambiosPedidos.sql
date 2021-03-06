SELECT c.asked_by_user_id, c.comentario_pedido, c.main_image_id, c.oficial_image_id, c.nombres as nombres_asked,
	c.apellidos as apellidos_asked, somebody_id, somebody_nombres, somebody_apellidos, 
	somebody_nota_id, somebody_nota_old, somebody_nota_new, somebody_image_id_to_delete, materia_to_remove_id, materia_to_add_id,
    c.asked_nota_id, c.nota_old, c.nota_new, c.rechazado_at, c.accepted_at, c.periodo_asked_id, c.year_asked_id, c.created_at,
    u.tipo, a.nombres, a.apellidos, 
    IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
    i2.nombre as foto_nombre_asked,
    i3.nombre as somebody_imagen_nombre_to_delete
FROM change_asked c
inner join users u on u.id=c.asked_by_user_id
inner join alumnos a on a.user_id=u.id
left join images i on i.id=a.foto_id and i.deleted_at is null
left join images i2 on i2.id=c.oficial_image_id and i2.deleted_at is null
left join images i3 on i3.id=c.somebody_image_id_to_delete and i3.deleted_at is null