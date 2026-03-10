<div class="container">
	<h2>Lista Instructor</h2>
	<form class="form-inline" action="?controller=instructor&action=search" method="post">
		<div class="form-group row">
			<div class="col-xs-4">
				<input class="form-control" id="id" name="id" type="text" placeholder="Busqueda por ID">
			</div>
		</div>
		<div class="form-group row">
			<div class="col-xs-4">
				<button type="submit" class="btn btn-primary" ><span class="glyphicon glyphicon-search"> </span> Buscar</button>
			</div>
		</div>
	</form>
	<div class="table-responsive">
		<table class="table table-hover">
			<thead>
				<tr>
					<th>Id</th>
					<th>Nombres</th>
					<th>Apellidos</th>
					<th>Estado</th>
					<th>Accion</th>
				</tr>
				<tbody>
					<?php foreach ($listaInstructor as$instructor) {?>

					
					<tr>
						<td> <a href="?controller=instructor&&action=updateshow&&idInstructor=<?php  echo $alumno->getId()?>"> <?php echo $alumno->getId(); ?></a> </td>
						<td><?php echo $instructor->getNombres(); ?></td>
						<td><?php echo $instructor->getApellidos(); ?></td>
						<td><?php if ( $instructor->getEstado()=='checked'):?>
							Activo
						<?php  else:?>
							Inactivo
						<?php endif; ?></td>
						<td><a href="?controller=instructor&&action=delete&&id=<?php echo $instructor->getId() ?>">Eliminar</a> </td>
					</tr>
					<?php } ?>
				</tbody>

			</thead>
		</table>

	</div>	

</div>