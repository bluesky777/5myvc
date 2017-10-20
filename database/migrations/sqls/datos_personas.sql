SELECT p.id as persona_id, p.nombres, p.apellidos, p.user_id, u.username, 
	("Pr") as tipo, p.sexo, u.email, 
    u.imagen_id, IFNULL(i.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
    p.foto_id, IFNULL(i2.nombre, IF(p.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
    "N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, "N/A" as year_id  
	from profesores p 
	inner join users u on p.user_id=u.id
    left join images i on i.id=u.imagen_id
    left join images i2 on i2.id=p.foto_id
union
select a.id as persona_id, a.nombres, a.apellidos, a.user_id, u.username, 
	("Al") as tipo, a.sexo, u.email, 
    u.imagen_id, IFNULL(i.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
    a.foto_id, IFNULL(i2.nombre, IF(a.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
    g.id as grupo_id, g.nombre as nombre_grupo, g.abrev as abrev_grupo, g.year_id
	from alumnos a 
	inner join users u on a.user_id=u.id
	inner join matriculas m on m.alumno_id=a.id and (m.estado="MATR" or m.estado="ASIS")
	inner join grupos g on g.id=m.grupo_id
    left join images i on i.id=u.imagen_id
    left join images i2 on i2.id=a.foto_id
union
SELECT ac.id as persona_id, ac.nombres, ac.apellidos, ac.user_id, u.username, 
	("Pr") as tipo, ac.sexo, u.email, 
    u.imagen_id, IFNULL(i.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
    ac.foto_id, IFNULL(i2.nombre, IF(ac.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
    "N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, "N/A" as year_id
	from acudientes ac 
	inner join users u on ac.user_id=u.id
    left join images i on i.id=u.imagen_id
    left join images i2 on i2.id=ac.foto_id
union
SELECT u.id as persona_id, "" as nombres, "" as apellidos, u.id as user_id, u.username,
	("Us") as tipo, u.sexo, u.email,
    u.imagen_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.png", "default_male.png")) as imagen_nombre, 
    u.imagen_id as foto_id, IFNULL(i.nombre, IF(u.sexo="F","default_female.png", "default_male.png")) as foto_nombre, 
    "N/A" as grupo_id, ("N/A") as nombre_grupo, ("N/A") as abrev_grupo, "N/A" as year_id  
    from users u
    left join images i on i.id=u.imagen_id 
