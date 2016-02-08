SELECT R2.alumno_id, R2.materia, R2.alias, AVG(R2.nota_asignatura) AS promedio, SUM(R2.perdidos) as perdidos
	FROM
    (
	SELECT R1.alumno_id, R1.materia, R1.alias, SUM(valor_nota) as nota_asignatura, R1.periodo_id, R1.asignatura_id,
		SUM(R1.perdido) as perdidos
    FROM
		(
        SELECT m.materia, m.alias, n.id as nota_id, n.nota, n.subunidad_id, n.alumno_id, 
			AVG((u.porcentaje/100)*((s.porcentaje/100)*n.nota)) as valor_nota, 
            IF(n.nota<70, 1, 0) as perdido, 
			s.definicion, s.porcentaje as porc_subuni, s.unidad_id, u.porcentaje as porc_uni, u.periodo_id, u.asignatura_id
        FROM notas n 
			inner join subunidades s on s.id=n.subunidad_id and s.deleted_at is null
			inner join unidades u on u.id=s.unidad_id and u.deleted_at is null
            inner join asignaturas a on a.id=u.asignatura_id and a.deleted_at is null
            inner join grupos g on g.id=a.grupo_id and g.year_id=1 and g.deleted_at is null
            inner join materias m on m.id=a.materia_id and m.deleted_at is null
        WHERE g.id = 10
        group by n.alumno_id, n.id
        order by a.orden
        )R1
	GROUP BY R1.alumno_id, R1.asignatura_id, R1.periodo_id
	)R2
group by R2.alumno_id, R2.asignatura_id