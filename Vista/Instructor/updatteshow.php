<div class="container">
	<h2>Actualizar Instructor</h2>
	<form action="?controller=instructor&&action=update" method="POST">
		<input type="hidden" name="id" value="<?php echo $instructor->getId() ?>" >
		<div class="form-group">
			<label for="text">Nombres</label>
			<input type="text" name="nombres" id="nombres" class="form-control" value="<?php echo $instructor->getNombres() ?>">
		</div>
		<div class="form-group">
			<label for="text">Apellidos</label>
			<input type="text" name="apellidos" id="apellidos" class="form-control" value="<?php echo $instructor->getApellidos(); ?>">
		</div>
		<div class="check-box">
			<label>Activo <input type="checkbox" name="estado" <?php echo $instructor->getEstado() == 'checked' ? 'checked' : '' ?>></label>
		</div>
		<button type="submit" class="btn btn-primary">Actualizar</button>

	</form>
</div>