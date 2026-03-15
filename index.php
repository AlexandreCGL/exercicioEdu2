<?php
$arquivo = "bancoDeDados.txt";

$nomes = [];
$categorias = [];
$quantidades = [];
$precos = [];

if (file_exists($arquivo)) {
    $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $dados = explode("|", $linha);

        if (count($dados) == 4) {
            $nomes[] = trim($dados[0]);
            $categorias[] = trim($dados[1]);
            $quantidades[] = (int) trim($dados[2]);
            $precos[] = (float) trim($dados[3]);
        }
    }
}

$acao = $_POST['acao'] ?? '';

if ($acao == 'salvar') {
    $nome = trim($_POST["nome"] ?? '');
    $categoria = trim($_POST["categoria"] ?? '');
    $qtd = trim($_POST["qtd"] ?? '');
    $preco = trim($_POST["preco"] ?? '');

    if ($nome == "" || $categoria == "" || $qtd == "" || $preco == "") {
        $mensagem = "<p>Preencha todos os campos.</p>";
    } elseif (count($nomes) >= 10) {
        $mensagem = "<p>Limite de 10 produtos atingido.</p>";
    } else {
        $linhaDeArquivos = "$nome|$categoria|$qtd|$preco" . PHP_EOL;
        file_put_contents($arquivo, $linhaDeArquivos, FILE_APPEND);

        $nomes[] = $nome;
        $categorias[] = $categoria;
        $quantidades[] = (int)$qtd;
        $precos[] = (float)$preco;

        $mensagem = "<p>Produto cadastrado com sucesso!</p>";
    }
}

if ($acao == 'sair') {
    $mensagem = "<p>Sistema finalizado.</p>";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Cadastro de Produtos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <h1>Sistema de Cadastro de Produtos</h1>

    <div class="menu">
        <form method="post">
            <button type="submit" name="acao" value="cadastrar">Cadastrar produto</button>
            <button type="submit" name="acao" value="listar">Listar produtos</button>
            <button type="submit" name="acao" value="buscar">Buscar produto pelo nome</button>
            <button type="submit" name="acao" value="estoque_baixo">Exibir estoque baixo</button>
            <button type="submit" name="acao" value="valor_total">Calcular valor total</button>
            <button type="submit" name="acao" value="sair">Sair</button>
        </form>
    </div>

    <?php
    if (!empty($mensagem)) {
        echo "<div class='resultado'>$mensagem</div>";
    }
    ?>

    <?php if ($acao == 'cadastrar' || $acao == '') { ?>
        <div class="container">
            <h2>Cadastrar Produto</h2>
            <form method="post">
                <input type="hidden" name="acao" value="salvar">

                <label for="nome">Nome do produto:</label>
                <input type="text" name="nome" id="nome">

                <label for="categoria">Categoria:</label>
                <select name="categoria" id="categoria">
                    <option value="Produtos novos">Produtos novos</option>
                    <option value="Produtos usados">Produtos usados</option>
                </select>

                <label for="qtd">Quantidade em estoque:</label>
                <input type="number" name="qtd" id="qtd" min="0">

                <label for="preco">Preço unitário:</label>
                <input type="number" name="preco" id="preco" min="0" step="0.01">

                <button type="submit">Cadastrar produto</button>
            </form>
        </div>
    <?php } ?>

    <div class="resultado">
        <?php
        if ($acao == 'listar') {
            if (count($nomes) == 0) {
                echo "<p>Não há produtos cadastrados para exibição.</p>";
            } else {
                echo "<h2>Lista de Produtos</h2>";
                echo "<table>";
                echo "<tr>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Preço</th>
                        <th>Total do item</th>
                      </tr>";

                for ($i = 0; $i < count($nomes); $i++) {
                    $totalItem = $quantidades[$i] * $precos[$i];

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($nomes[$i]) . "</td>";
                    echo "<td>" . htmlspecialchars($categorias[$i]) . "</td>";
                    echo "<td>" . $quantidades[$i] . "</td>";
                    echo "<td>R$ " . number_format($precos[$i], 2, ',', '.') . "</td>";
                    echo "<td>R$ " . number_format($totalItem, 2, ',', '.') . "</td>";
                    echo "</tr>";
                }

                echo "</table>";
            }
        }

        if ($acao == 'buscar') {
            ?>
            <h2>Buscar produto pelo nome</h2>
            <form method="post">
                <input type="hidden" name="acao" value="buscar_resultado">
                <label for="nome_busca">Digite o nome do produto:</label>
                <input type="text" name="nome_busca" id="nome_busca">
                <button type="submit">Buscar</button>
            </form>
            <?php
        }

        if ($acao == 'buscar_resultado') {
            $nomeBusca = trim($_POST['nome_busca'] ?? '');
            $encontrado = false;

            if ($nomeBusca == '') {
                echo "<p>Digite um nome para buscar.</p>";
            } else {
                for ($i = 0; $i < count($nomes); $i++) {
                    if (strtolower($nomes[$i]) == strtolower($nomeBusca)) {
                        $totalItem = $quantidades[$i] * $precos[$i];

                        echo "<h2>Produto encontrado</h2>";
                        echo "<p><strong>Nome:</strong> " . htmlspecialchars($nomes[$i]) . "</p>";
                        echo "<p><strong>Categoria:</strong> " . htmlspecialchars($categorias[$i]) . "</p>";
                        echo "<p><strong>Quantidade:</strong> " . $quantidades[$i] . "</p>";
                        echo "<p><strong>Preço:</strong> R$ " . number_format($precos[$i], 2, ',', '.') . "</p>";
                        echo "<p><strong>Total em estoque:</strong> R$ " . number_format($totalItem, 2, ',', '.') . "</p>";

                        $encontrado = true;
                        break;
                    }
                }

                if (!$encontrado) {
                    echo "<p>Produto não localizado.</p>";
                }
            }
        }

        if ($acao == 'estoque_baixo') {
            $temBaixo = false;

            for ($i = 0; $i < count($nomes); $i++) {
                if ($quantidades[$i] < 5) {
                    if (!$temBaixo) {
                        echo "<h2>Produtos com estoque baixo</h2>";
                        echo "<table>";
                        echo "<tr>
                                <th>Nome</th>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Preço</th>
                              </tr>";
                    }

                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($nomes[$i]) . "</td>";
                    echo "<td>" . htmlspecialchars($categorias[$i]) . "</td>";
                    echo "<td>" . $quantidades[$i] . "</td>";
                    echo "<td>R$ " . number_format($precos[$i], 2, ',', '.') . "</td>";
                    echo "</tr>";

                    $temBaixo = true;
                }
            }

            if ($temBaixo) {
                echo "</table>";
            } else {
                echo "<p>Não existem produtos com estoque baixo.</p>";
            }
        }

        if ($acao == 'valor_total') {
            if (count($nomes) == 0) {
                echo "<p>Não é possível realizar o cálculo. Não há produtos cadastrados.</p>";
            } else {
                $valorTotalGeral = 0;

                for ($i = 0; $i < count($nomes); $i++) {
                    $valorTotalGeral += $quantidades[$i] * $precos[$i];
                }

                echo "<h2>Valor total geral do estoque</h2>";
                echo "<p><strong>R$ " . number_format($valorTotalGeral, 2, ',', '.') . "</strong></p>";
            }
        }

        if ($acao == 'sair') {
            echo "<p>Obrigado por usar o sistema.</p>";
        }
        ?>
    </div>

</body>
</html>