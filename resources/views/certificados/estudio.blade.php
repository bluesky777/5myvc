<head>
<title>Informe PDF MyVc</title>
<meta charset="utf8" />
<link rel="stylesheet" type="text/css" href="{{ asset('css/parapdf.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap/css/bootstrap.min.css') }}">


</head>


@foreach ($alumnos as $alumno)
	<div>
		<div class="page-vertical">	
			<div class="row">
				<div class="col-lg-12 col-xs-12">
					<div class="row encabezado-boletin">
						<div class="col-lg-12 col-xs-12">
							<div class="row">
								<div class="col-lg-2 col-xs-2">
									<img class="img-responsive img-thumbnail foto-alumno" src="{{ $User::$perfilPath . $year->logo }}" />
									</div>
								<div class="col-lg-8 col-xs-8 title-encabezado-boletin">
									<div class="nombre-colegio">
										{{$year->nombre_colegio . ' - ' . $year->abrev_colegio}}</div>
									<div class="resolucion-colegio">
										{{$year->resolucion}}</div>
									<div class="title-descripcion">
										Boletin FINAL {{$year->year}}</div>
								</div>
								<div class="col-lg-2.col-xs-2">
									<img class="img-responsive img-thumbnail foto-alumno" src="{{ $User::$perfilPath . $alumno->foto_nombre}}"  />
								</div>
							</div>
							<div class="row.descripcion-encabezado">
								<div class="col-lg-6 col-xs-6">
									<div class="grupo-alumno">
										Grupo: 
										<b> {{$grupo->nombre_grupo}}</b>
									</div>
									<div class="titular-grupo" popover="<img src='@{{perfilPath + grupo.foto_nombre}}' style='width: 150px; height: 150px;'>" 
											popover-popup-delay="1000" popover-title="@{{grupo.nombres_profesor + ' ' + grupo.apellidos_profesor}}" popover-animation="true" 
											popover-trigger="mouseenter">
										Titular: Pr. {{$grupo->nombres_profesor . ' ' . $grupo->apellidos_profesor}}
									</div>
								</div>
								<div class="col-lg-6 col-xs-6">
									<h5 class="nombre-alumno"> {{$alumno->nombres . ' ' . $alumno->apellidos}}</h5>
									<div class="promedio-alumno"> Puntaje: {{round($alumno->promedio, 1)}}% - Puesto: {{$alumno->puesto}}/{{$grupo->cantidad_alumnos}}</div>
								</div>
							</div>
						</div>
					</div>


					<div class="row body-boletin" style="margin-top: 10px; margin-bottom: 10px; ">
						<div class="col-lg-12 col-xs-12">
							<table class="table-bordered table-striped" style="margin: 0 auto;">
								<thead>
									<tr style="border-bottom: 2px solid;">
										<th>No</th>
										<th>Nombres</th>
										<th> 
											<i class="fa fa-clock-o icon-tardanza"></i>
											A</th>
										@foreach ($year->periodos as $periodo)
											<th> Per{{$periodo->numero}}</th>
										@endforeach
										<th style="border-left: 2px solid !important;"> Prom</th>
									</tr>
								</thead>
								<tbody>
									<?php 
									for ($i=0; $i < count($alumno->asignaturas); $i++) { 
									?>
										<tr>
											<td><?php echo $i + 1; ?></td>
											<td><?php echo $alumno->asignaturas[$i]->materia; ?></td>
											<td><?php print_r($alumno->asignaturas[$i]->definitivas); ?></td>
											
											
										</tr>
									<?php 
									} 
									?>
								</tbody>

								<tfoot>
									<tr style="border-top: 2px solid;">
										<td> - </td>
										<td> Total</td>

										<td> {{$alumno->ausencias}}</td>

										@foreach($year->periodos as $periodo)
											<td></td>
										@endforeach

										<td style="font-weight: bold; border-left: 2px solid !important;">
											{{round($alumno->promedio, 1) }}

											@if($alumno->notas_perdidas > 0)
												<span class="cant-unidades-perdidas">
													({{ $alumno->notas_perdidas }})
												</span>
											@endif
										</td>
										
									</tr>
								</tfoot>
							</table>

						</div>
					</div>

					<div class="row">
						<div class="col-lg-12.col-xs-12">
							<h4 style="text-align:center; font-weight: bold">
								@if ($alumno->sexo == 'F')
									<span> La alumna </span>
								@else
									<span> El alumno </span>
								@endif

								@if ($alumno->cant_lost_asig > 1)
									<span style="font-weight:bold"> NO 
										ha sido </span>
								@else
									<span> ha sido </span>
								@endif

								

								@if ($alumno->sexo == 'F')
									<span> promovida.</span>
								@else
									<span> promovido.</span>
								@endif
							</h4>
						</div>
					</div>



					<div class="row.footer-boletin">
						<div class="col-lg-12 col-xs-12">
							<div class="row">
								<div class="col-lg-6 col-xs-6 rector-firma">
									<img ng-show="config show_firma_rector" ng-src="@{{perfilPath + year.rector_firma}}" />
									</div>
								<div class="col-lg-6 col-xs-6 titular-firma">
									<img ng-show="config.show_firma_titular" ng-src="@{{perfilPath + grupo.firma_titular_nombre}}" />
									</div>
							</div>
							<div class="row">
								<div class="col-lg-6 col-xs-6 nombre-rector-firma">
									{{$year->nombres_rector}} {{$year->apellidos_rector}}
									</div>
								<div class="col-lg-6 col-xs-6 nombre-titular-firma">
									{{$grupo->nombres_profesor}} {{$grupo->apellidos_profesor}}
									</div>
							</div>

							<div class="row">
								@if ($year->sexo_rector=='M')
									<div class="col-lg-6 col-xs-6">
										Rector
										</div>
								@else
									<div class="col-lg-6 col-xs-6">
										Rectora
										</div>
								@endif
							

								<div class="col-lg-6.col-xs-6">
									Titular
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endforeach

