<?php
$config = require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (in_array($action, ['create', 'update'], true)) {
        $anexoNome = handleUpload($_FILES['anexo'] ?? [], $config);
        $isParcelada = isset($_POST['parcelada']) ? 1 : 0;
        $numeroParcelas = $isParcelada ? (int)($_POST['numero_parcelas'] ?? 0) : null;
        $numeroParcelas = ($numeroParcelas && $numeroParcelas > 1) ? $numeroParcelas : null;

        $data = [
            trim($_POST['descricao']),
            (int)$_POST['categoria_id'],
            (int)$_POST['responsavel_id'],
            (float)$_POST['valor'],
            $_POST['data_prevista'],
            $_POST['data_recebimento'] ?: null,
            $_POST['status'],
            trim($_POST['observacoes']),
            isset($_POST['recorrente']) ? 1 : 0,
            $anexoNome ?: ($_POST['anexo_atual'] ?? null),
            $numeroParcelas
        ];
    }

    if ($action === 'create') {
        $pdo->prepare('INSERT INTO contas_receber (descricao,categoria_id,responsavel_id,valor,data_prevista,data_recebimento,status,observacoes,recorrente,anexo,numero_parcelas) VALUES (?,?,?,?,?,?,?,?,?,?,?)')->execute($data);
    }

    if ($action === 'update') {
        $id = (int)$_POST['id'];
        $data[] = $id;
        $pdo->prepare('UPDATE contas_receber SET descricao=?,categoria_id=?,responsavel_id=?,valor=?,data_prevista=?,data_recebimento=?,status=?,observacoes=?,recorrente=?,anexo=?,numero_parcelas=? WHERE id=?')->execute($data);
    }

    if ($action === 'delete') {
        $pdo->prepare('DELETE FROM contas_receber WHERE id=?')->execute([(int)$_POST['id']]);
    }

    if ($action === 'mark_received') {
        $pdo->prepare("UPDATE contas_receber SET status='Recebido', data_recebimento=CURDATE() WHERE id=?")->execute([(int)$_POST['id']]);
    }

    flash('success', 'Operação concluída em contas a receber.');
    redirect('index.php?module=contas_receber');
}

$fMes = $_GET['mes'] ?? '';
$fCategoria = $_GET['categoria'] ?? '';
$fStatus = $_GET['status'] ?? '';
$fBusca = trim($_GET['q'] ?? '');
$fRec = $_GET['recorrente'] ?? '';
$showFilters = isset($_GET['show_filters']) && $_GET['show_filters'] === '1';

$where = ['1=1'];
$params = [];
if ($fMes) { $where[] = "DATE_FORMAT(data_prevista,'%Y-%m')=?"; $params[] = $fMes; }
if ($fCategoria) { $where[] = 'categoria_id=?'; $params[] = $fCategoria; }
if ($fStatus) { $where[] = 'status=?'; $params[] = $fStatus; }
if ($fBusca) { $where[] = 'descricao LIKE ?'; $params[] = "%$fBusca%"; }
if ($fRec !== '') { $where[] = 'recorrente=?'; $params[] = $fRec; }
$ws = implode(' AND ', $where);

$contas = $pdo->prepare("SELECT cr.*, c.nome categoria, r.nome responsavel FROM contas_receber cr LEFT JOIN categorias c ON c.id=cr.categoria_id LEFT JOIN responsaveis r ON r.id=cr.responsavel_id WHERE $ws ORDER BY cr.data_prevista DESC");
$contas->execute($params);
$contas = $contas->fetchAll();
$categorias = $pdo->query('SELECT id,nome FROM categorias ORDER BY nome')->fetchAll();
$responsaveis = $pdo->query('SELECT id,nome FROM responsaveis ORDER BY nome')->fetchAll();
$statusList = ['Pendente', 'Recebido', 'Vencido', 'Cancelado'];
?>

<div class="card">
    <div class="card-header-row">
        <h3>Filtros</h3>
        <a class="btn-secondary" href="index.php?module=contas_receber&show_filters=<?= $showFilters ? '0' : '1' ?>"> <?= $showFilters ? 'Esconder filtros' : 'Exibir filtros' ?> </a>
    </div>
    <?php if ($showFilters): ?>
        <form class="filters">
            <input type="hidden" name="module" value="contas_receber">
            <input type="hidden" name="show_filters" value="1">
            <input type="month" name="mes" value="<?= esc($fMes) ?>">
            <select name="categoria"><option value="">Categoria</option><?php foreach($categorias as $c): ?><option value="<?= $c['id'] ?>" <?= $fCategoria == $c['id'] ? 'selected' : '' ?>><?= esc($c['nome']) ?></option><?php endforeach; ?></select>
            <select name="status"><option value="">Status</option><?php foreach($statusList as $s): ?><option <?= $fStatus === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
            <select name="recorrente"><option value="">Recorrente?</option><option value="1" <?= $fRec === '1' ? 'selected' : '' ?>>Sim</option><option value="0" <?= $fRec === '0' ? 'selected' : '' ?>>Não</option></select>
            <input name="q" value="<?= esc($fBusca) ?>" placeholder="Buscar">
            <button class="btn-secondary" type="submit">Filtrar</button>
        </form>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header-row">
        <h3>Listagem</h3>
        <button class="btn-primary" type="button" data-modal-target="modal-conta-receber-create">Adicionar nova conta</button>
    </div>
    <div class="table-wrap">
        <table>
            <tr><th>Descrição</th><th>Categoria</th><th>Prevista</th><th>Valor</th><th>Status</th><th>Ações</th></tr>
            <?php foreach($contas as $c): ?>
                <tr>
                    <td><?= esc($c['descricao']) ?></td>
                    <td><?= esc($c['categoria']) ?></td>
                    <td><?= date('d/m/Y', strtotime($c['data_prevista'])) ?></td>
                    <td>R$ <?= number_format($c['valor'], 2, ',', '.') ?></td>
                    <td><span class="badge <?= statusBadgeClass($c['status']) ?>"><?= esc($c['status']) ?></span></td>
                    <td class="actions">
                        <button class="btn-secondary" type="button" data-modal-target="modal-conta-receber-edit-<?= $c['id'] ?>">Editar</button>
                        <form method="post"><input type="hidden" name="action" value="mark_received"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn-success" type="submit">Recebido</button></form>
                        <form method="post" onsubmit="return confirm('Excluir conta?')"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>"><button class="btn-danger" type="submit">Excluir</button></form>
                        <?php if($c['anexo']): ?><a class="btn-link" target="_blank" href="uploads/<?= esc($c['anexo']) ?>">Anexo</a><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

<div class="modal" id="modal-conta-receber-create" aria-hidden="true">
    <div class="modal-content modal-lg">
        <div class="modal-header"><h3>Adicionar nova conta</h3><button class="modal-close" type="button" data-modal-close>&times;</button></div>
        <form method="post" enctype="multipart/form-data" class="grid-form">
            <input type="hidden" name="action" value="create">
            <input name="descricao" placeholder="Descrição" required>
            <select name="categoria_id"><?php foreach($categorias as $cat): ?><option value="<?= $cat['id'] ?>"><?= esc($cat['nome']) ?></option><?php endforeach; ?></select>
            <select name="responsavel_id"><?php foreach($responsaveis as $r): ?><option value="<?= $r['id'] ?>"><?= esc($r['nome']) ?></option><?php endforeach; ?></select>
            <input type="number" step="0.01" name="valor" placeholder="Valor" required>
            <input type="date" name="data_prevista" required>
            <input type="date" name="data_recebimento">
            <select name="status"><?php foreach($statusList as $s): ?><option><?= $s ?></option><?php endforeach; ?></select>
            <label><input type="checkbox" name="recorrente"> Conta recorrente?</label>
            <label><input type="checkbox" name="parcelada" data-toggle-target="create-receber-parcelas"> Conta parcelada?</label>
            <div data-toggle-id="create-receber-parcelas" class="toggle-extra hidden"><input type="number" min="2" name="numero_parcelas" placeholder="Número de parcelas"></div>
            <input type="file" name="anexo" accept=".pdf,image/*">
            <textarea name="observacoes" placeholder="Observações"></textarea>
            <div class="modal-actions">
                <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                <button class="btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>
</div>

<?php foreach($contas as $c): ?>
    <div class="modal" id="modal-conta-receber-edit-<?= $c['id'] ?>" aria-hidden="true">
        <div class="modal-content modal-lg">
            <div class="modal-header"><h3>Editar conta</h3><button class="modal-close" type="button" data-modal-close>&times;</button></div>
            <form method="post" enctype="multipart/form-data" class="grid-form">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                <input type="hidden" name="anexo_atual" value="<?= esc($c['anexo']) ?>">
                <input name="descricao" value="<?= esc($c['descricao']) ?>" required>
                <select name="categoria_id"><?php foreach($categorias as $cat): ?><option value="<?= $cat['id'] ?>" <?= $c['categoria_id'] == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['nome']) ?></option><?php endforeach; ?></select>
                <select name="responsavel_id"><?php foreach($responsaveis as $r): ?><option value="<?= $r['id'] ?>" <?= $c['responsavel_id'] == $r['id'] ? 'selected' : '' ?>><?= esc($r['nome']) ?></option><?php endforeach; ?></select>
                <input type="number" step="0.01" name="valor" value="<?= esc($c['valor']) ?>" required>
                <input type="date" name="data_prevista" value="<?= esc($c['data_prevista']) ?>" required>
                <input type="date" name="data_recebimento" value="<?= esc($c['data_recebimento']) ?>">
                <select name="status"><?php foreach($statusList as $s): ?><option <?= $c['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select>
                <label><input type="checkbox" name="recorrente" <?= $c['recorrente'] ? 'checked' : '' ?>> Conta recorrente?</label>
                <label><input type="checkbox" name="parcelada" data-toggle-target="edit-receber-parcelas-<?= $c['id'] ?>" <?= (int)$c['numero_parcelas'] > 1 ? 'checked' : '' ?>> Conta parcelada?</label>
                <div data-toggle-id="edit-receber-parcelas-<?= $c['id'] ?>" class="toggle-extra <?= (int)$c['numero_parcelas'] > 1 ? '' : 'hidden' ?>"><input type="number" min="2" name="numero_parcelas" value="<?= (int)$c['numero_parcelas'] ?: '' ?>" placeholder="Número de parcelas"></div>
                <input type="file" name="anexo" accept=".pdf,image/*">
                <textarea name="observacoes" placeholder="Observações"><?= esc($c['observacoes']) ?></textarea>
                <div class="modal-actions">
                    <button class="btn-secondary" type="button" data-modal-close>Cancelar</button>
                    <button class="btn-primary" type="submit">Salvar alterações</button>
                </div>
            </form>
        </div>
    </div>
<?php endforeach; ?>
