<div class="container-fluid py-4">
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0 fw-bold text-success">Gestión de Usuarios</h2>
        <button class="btn btn-success">
          <i class="fas fa-plus me-2"></i>Nuevo Usuario
        </button>
      </div>

      <!-- Sección de búsqueda y filtros -->
      <div class="search-section">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-light border-end-0">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" id="search-input" class="form-control border-start-0" placeholder="Buscar por email o ID...">
            </div>
          </div>
          <div class="col-md-3">
            <select id="verified-filter" class="form-select">
              <option value="all">Todos los estados</option>
              <option value="verified">Verificados</option>
              <option value="not-verified">No verificados</option>
            </select>
          </div>
          <div class="col-md-3">
            <select id="role-filter" class="form-select">
              <option value="all">Todos los roles</option>
              <?php foreach ($roles as $role): ?>
                <option value="<?= htmlspecialchars($role['nombre']) ?>">
                  <?= htmlspecialchars($role['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="results-info mt-2" id="results-info">
          Mostrando todos los usuarios
        </div>
      </div>

      <!-- Tabla de usuarios -->
      <div class="table-container">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Email</th>
              <th>Verificado</th>
              <th>Rol</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="users-table-body">
            <?php foreach ($users as $user): ?>
              <tr>
                <td><?= htmlspecialchars($user['idUser']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td>
                  <?php if ($user['esVerificado'] == 1): ?>
                    <span class="badge bg-success">✔ Sí</span>
                  <?php else: ?>
                    <span class="badge bg-danger">✘ No</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user['nombre_rol']) ?></td>
                <td>
                  <form class="user-form" method="post" action="<?= APP_URL . 'usuarios/update' ?>">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($user['idUser']) ?>">

                    <div class="form-group">
                      <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                        class="form-control form-control-sm" style="width: 180px;">
                    </div>

                    <div class="form-group">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="esVerificado"
                          value="1" <?= $user['esVerificado'] ? 'checked' : '' ?>>
                        <label class="form-check-label small">Verificado</label>
                      </div>
                    </div>

                    <div class="form-group">
                      <select name="rol_id" class="form-select form-select-sm" style="width: 150px;" required>
                        <option value="">Seleccione rol</option>
                        <?php foreach ($roles as $role): ?>
                          <option value="<?= $role['idRol'] ?>"
                            <?= ($role['nombre'] == $user['nombre_rol']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['nombre']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>

                    <button type="submit" class="btn btn-success btn-sm">Guardar</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <div id="no-results" class="no-results" style="display: none;">
          <i class="fas fa-search fa-2x mb-3 text-muted"></i>
          <p>No se encontraron usuarios que coincidan con los criterios de búsqueda.</p>
        </div>
      </div>
    </div>
  </div>
</div>



