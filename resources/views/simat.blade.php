<h2>{{$grupo->nombre}} - {{$grupo->nombres_titular}} {{$grupo->apellidos_titular}}</h2>
<table >
    <thead>
        <tr>
            <th style="background-color: #fff7ad">No</th>
            <th style="background-color: #fff7ad">ID</th>
            <th style="background-color: #fff7ad">Tipo de Documento</th>
            <th style="background-color: #fff7ad">Nro de documento</th>
            <th style="background-color: #fff7ad">Lugar de Expedición Departamento</th>
            <th style="background-color: #fff7ad">Lugar de Expedición Municipio</th>
            <th style="background-color: #fff7ad">Primer apellido</th>
            <th style="background-color: #fff7ad">Segundo apellido</th>
            <th style="background-color: #fff7ad">Primer nombre</th>
            <th style="background-color: #fff7ad">Segundo nombre</th>
            <th style="background-color: #fff7ad">Estado Matricula</th>
            <th style="background-color: #fff7ad">Dirección residencia</th>
            <th style="background-color: #fff7ad">Teléfono</th>
            <th style="background-color: #fff7ad">Estrato</th>
            <th style="background-color: #fff7ad">SISBEN</th>
            <th style="background-color: #fff7ad">Fecha de nac</th>
            <th style="background-color: #fff7ad">Departamento nacimiento</th>
            <th style="background-color: #fff7ad">Municipio nacimiento</th>
            <th style="background-color: #fff7ad">Sexo</th>
            <th style="background-color: #fff7ad">Nuevo</th>
            
            <th style="background-color: #e0f1ff">Nombres Acud1</th>
            <th style="background-color: #e0f1ff">Apellidos Acud1</th>
            <th style="background-color: #e0f1ff">Sexo Acud1</th>
            <th style="background-color: #e0f1ff">Tipo docu Acud1</th>
            <th style="background-color: #e0f1ff">¿Es el acudiente? Acud1</th>
            <th style="background-color: #e0f1ff">Documento Acud1</th>
            <th style="background-color: #e0f1ff">Departam Docu Acud1</th>
            <th style="background-color: #e0f1ff">Ciudad Docu Acud1</th>
            
            <th style="background-color: #c9f893">Nombres Acud2</th>
            <th style="background-color: #c9f893">Apellidos Acud2</th>
            <th style="background-color: #c9f893">Sexo Acud2</th>
            <th style="background-color: #c9f893">Tipo docu Acud2</th>
            <th style="background-color: #c9f893">¿Es el acudiente? Acud2</th>
            <th style="background-color: #c9f893">Documento Acud2</th>
            <th style="background-color: #c9f893">Departam Docu Acud2</th>
            <th style="background-color: #c9f893">Ciudad Docu Acud2</th>
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
            <td>{{$alumno->direccion}}</td>
            <td>{{$alumno->telefono}}</td>
            <td>{{$alumno->estrato}}</td>
            <td>{{$alumno->sisben}}</td>
            <td>{{$alumno->fecha_nac}}</td>
            <td>{{$alumno->departamento_nac_nombre}}</td>
            <td>{{$alumno->ciudad_nac_nombre}}</td>
            <td>{{$alumno->sexo}}</td>
            <td>{{$alumno->es_nuevo}}</td>
            
            <td>{{$alumno->acudientes[0]->nombres}}</td>
            <td>{{$alumno->acudientes[0]->apellidos}}</td>
            <td>{{$alumno->acudientes[0]->sexo}}</td>
            <td>{{$alumno->acudientes[0]->tipo_doc_nombre}}</td>
            <td>{{$alumno->acudientes[0]->es_acudiente}}</td>
            <td>{{$alumno->acudientes[0]->documento}}</td>
            <td>{{$alumno->acudientes[0]->departamento_doc_nombre}}</td>
            <td>{{$alumno->acudientes[0]->ciudad_doc_nombre}}</td>
            
            <td>{{$alumno->acudientes[1]->nombres}}</td>
            <td>{{$alumno->acudientes[1]->apellidos}}</td>
            <td>{{$alumno->acudientes[1]->sexo}}</td>
            <td>{{$alumno->acudientes[1]->tipo_doc_nombre}}</td>
            <td>{{$alumno->acudientes[1]->es_acudiente}}</td>
            <td>{{$alumno->acudientes[1]->documento}}</td>
            <td>{{$alumno->acudientes[1]->departamento_doc_nombre}}</td>
            <td>{{$alumno->acudientes[1]->ciudad_doc_nombre}}</td>
        </tr>
        @endforeach
    </tbody>
</table>