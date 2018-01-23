<h2>{{$grupo->nombre}} - {{$grupo->nombres_titular}} {{$grupo->apellidos_titular}}</h2>
<table >
    <thead>
        <tr>
            <th>No</th>
            <th>ID</th>
            <th>Tipo de Documento</th>
            <th>Nro de documento</th>
            <th>Lugar de Expedición Departamento</th>
            <th>Lugar de Expedición Municipio</th>
            <th>Primer apellido</th>
            <th>Segundo apellido</th>
            <th>Primer nombre</th>
            <th>Segundo nombre</th>
            <th>Estado Matricula</th>
            <th>Dirección residencia</th>
            <th>Teléfono</th>
            <th>Estrato</th>
            <th>SISBEN</th>
            <th>Fecha de nac</th>
            <th>Departamento nacimiento</th>
            <th>Municipio nacimiento</th>
            <th>Sexo</th>
        </tr>
    </thead>
    <tbody>
        @foreach($alumnos as $key=>$alumno)
        <tr>
            <td>{{++$key}}</td>
            <td>{{$alumno->alumno_id}}</td>
            <td>{{$alumno->tipo_doc_name}}</td>
            <td>{{$alumno->documento}}</td>
            <td>{{$alumno->departamento_doc_nombre}}</td>
            <td>{{$alumno->ciudad_doc_nombre}}</td>
            <td>{{$alumno->apellidos_divididos['first']}}</td>
            <td>{{$alumno->apellidos_divididos['last']}}</td>
            <td>{{$alumno->nombres_divididos['first']}}</td>
            <td>{{$alumno->nombres_divididos['last']}}</td>
            <td>{{$alumno->estado}}</td>
            <td>{{$alumno->telefono}}</td>
            <td>{{$alumno->estrato}}</td>
            <td>{{$alumno->sisben}}</td>
            <td>{{$alumno->fecha_nac}}</td>
            <td>{{$alumno->fecha_nac}}</td>
            <td>{{$alumno->departamento_nac_nombre}}</td>
            <td>{{$alumno->ciudad_nac_nombre}}</td>
            <td>{{$alumno->sexo}}</td>
        </tr>
        @endforeach
    </tbody>
</table>