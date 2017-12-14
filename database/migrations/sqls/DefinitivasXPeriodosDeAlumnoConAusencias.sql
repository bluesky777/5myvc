SELECT alumno_id, asignatura_id, periodo_id, numero_periodo,
	creditos, sum( ValorUnidad ) DefMateria, cantidad_ausencia, cantidad_tardanza 
FROM(
	SELECT n.alumno_id, a.id as asignatura_id, a.profesor_id, 
		a.creditos, u.periodo_id, u.definicion, u.id as unidad_id, u.porcentaje as porc_unidad, 
		s.id as subunidad_id, s.definicion as definicion_subunidad, s.porcentaje as porcentaje_subunidad, p.numero as numero_periodo, 
		sum( ((u.porcentaje/100)*((s.porcentaje/100)*n.nota)) ) ValorUnidad,
		aus.cantidad_ausencia, tar.cantidad_tardanza
	FROM asignaturas a 
	inner join unidades u on u.asignatura_id=a.id and u.deleted_at is null
	inner join subunidades s on s.unidad_id=u.id and s.deleted_at is null
	inner join notas n on n.subunidad_id=s.id and n.alumno_id=54 and n.deleted_at is null
	inner join periodos p on p.year_id=1 and p.id=u.periodo_id and p.deleted_at is null
	left join (
		select count(au.id) as cantidad_ausencia, au.alumno_id, au.periodo_id, au.asignatura_id
		from ausencias au 
		where au.deleted_at is null and au.cantidad_ausencia > 0
		group by au.alumno_id
		
		)as aus on aus.alumno_id=n.alumno_id and aus.asignatura_id=a.id and aus.periodo_id=p.id
	left join (
		select count(au.id) as cantidad_tardanza, au.alumno_id, au.periodo_id, au.asignatura_id
		from ausencias au 
		where au.deleted_at is null and au.cantidad_tardanza > 0
		group by au.alumno_id
		
		)as tar on tar.alumno_id=n.alumno_id and tar.asignatura_id=a.id and tar.periodo_id=p.id
	where a.grupo_id=10 and a.deleted_at is null and a.id=2
	group by n.alumno_id, s.unidad_id
)r
group by alumno_id, asignatura_id, periodo_id
order by numero_periodo, asignatura_id, periodo_id