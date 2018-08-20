<?php
include('../connection/connection.php');

ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
//Cria a sessão e verifica se o usuário está logado
session_start();
if (!isset($_SESSION['logado']) && !isset($_SESSION['idSave'])) {
    header("Location: ../cadastro/login.php?erro_login=1");
}

	$id = $_GET['id'];

	//Realiza uma busca no banco de dados para listar os projetos de um professor em específico
	$scriptSQL = "SELECT *
								FROM projeto
								WHERE id_projeto = ".$id;

	$result = $conn->query($scriptSQL);
	$vetor = $result->fetch_object();


	if (isset($_POST['remover_dados'])) {
		$upd = "UPDATE `interbccs`.`projeto`
		SET	`enable` = '0'
		WHERE id_projeto = ".$id.";";

		if (mysqli_query($conn, $upd) == TRUE) {
			
			$_SESSION['mensagem'] = "Projeto removido com sucesso!";
			header('Location: ./home.php');
		}
		else {
			?>
			<script>
				alert("Ocorreu um erro inesperado");
			</script>
			<?php
		}
	}

	if (isset($_POST['salvar_dados'])) {

		if (isset($_FILES["file"]["type"])) {
			$validextensions = array("jpeg", "jpg", "png");
			$temporary = explode(".", $_FILES["file"]["name"]);
			$file_extension = end($temporary);

			if (in_array($file_extension, $validextensions)) {//Verifica se está de acordo com a extensão
				if ($_FILES["file"]["error"] > 0) {

				} else {

					$novoNome = uniqid(time()) . '.' . $file_extension;
					$destino = '../Imagens/' . $novoNome;
					$sourcePath = $_FILES['file']['tmp_name']; // Storing source path of the file in a variable

					move_uploaded_file($sourcePath, $destino); // Moving Uploaded file
				}
			}
    }

    if (isset($_POST['photo_change']) && $_POST['photo_change'] != "") {
        if (isset($vetor->foto) && $vetor->foto != "") {
            $file = "../Imagens/" . $vetor->foto;
            if (file_exists($file)) {
               unlink($file);
            }
        }
    }	
		
    if (!isset($novoNome) || $novoNome == "") {
        $novoNome = $vetor->foto;
    }
		
		$var_alunos = 0;
		$var_site = 0;
		//Validação dos campos
		//Site
		if (isset($_POST['site'])) {
			$var_site = 1;
		}
		//Alunos
		if (isset($_POST['alunos'])) {
			$var_alunos = 1;
		}
		
		if ($var_site) {
			if ($var_alunos) {
				$insertSQL = "UPDATE `interbccs`.`projeto`
				SET `nome` = '".$_POST['nome']."', `descricao` = '".$_POST['descricao']."', `data_inicio` = '".$_POST['data']."', `concluido` = '".$_POST['andamento']."', `site_proj` = '".$_POST['site']."', `alunos` = '".$_POST['alunos']."', `foto` = '".$novoNome."'
				WHERE id_projeto = ".$id.";";				
			}
			else {
				$insertSQL = "UPDATE `interbccs`.`projeto`
				SET `nome` = '".$_POST['nome']."', `descricao` = '".$_POST['descricao']."', `data_inicio` = '".$_POST['data']."', `concluido` = '".$_POST['andamento']."', `site_proj` = '".$_POST['site']."', `foto` = '".$novoNome."'
				WHERE id_projeto = ".$id.";";					
			}
		}
		else {
			if ($var_alunos) {
				$insertSQL = "UPDATE `interbccs`.`projeto`
				SET `nome` = '".$_POST['nome']."', `descricao` = '".$_POST['descricao']."', `data_inicio` = '".$_POST['data']."', `concluido` = '".$_POST['andamento']."', `alunos` = '".$_POST['alunos']."', `foto` = '".$novoNome."'
				WHERE id_projeto = ".$id.";";						
			}
			else {
				$insertSQL = "UPDATE `interbccs`.`projeto`
				SET `nome` = '".$_POST['nome']."', `descricao` = '".$_POST['descricao']."', `data_inicio` = '".$_POST['data']."', `concluido` = '".$_POST['andamento']."', `foto` = '".$novoNome."'
				WHERE id_projeto = ".$id.";";					
			}		
		}
		
		if (mysqli_query($conn, $insertSQL) == TRUE) {
			
			$scriptSQL = "SELECT *
										FROM projeto
										WHERE id_projeto = ".$id;

			$result = $conn->query($scriptSQL);
			$vetor = $result->fetch_object();			
		}
		else {
			?>
			<script>
				alert("Ocorreu um erro inesperado");
			</script>
			<?php
		}

		//Update dos dados das áreas
		$selArea = "SELECT `id_area`
		FROM `area_proj`
		WHERE `id_projeto` ='".$id."'";
		
		$sel = $conn->query($selArea);
		$objArea = $sel->fetch_object();
		
		if (count($_POST['checkarea']) != NULL) {
			foreach ($_POST['checkarea'] as $key => $value) {
					for (;$objArea->id_area != NULL && $objArea->id_area < $value;) {
						//Remove a tupla
						$del_area = "DELETE FROM `interbccs`.`area_proj` WHERE id_area =".$objArea->id_area." AND id_projeto =".$id;
						$conn->query($del_area);
						//Incrementa o obj
						if ($objArea->id_area != NULL) {
							$objArea = $sel->fetch_object();
						}
					}
					//Verifica se o id encontrado precisa ser inserido
					if ($objArea->id_area != NULL && $value < $objArea->id_area) {
						$ins_area = "INSERT INTO `interbccs`.`area_proj` (`id_area`, `id_projeto`) VALUE ('".$value."', '".$id."')";
						$conn->query($ins_area);
					}
					else if ($objArea->id_area != NULL && $objArea->id_area == $value) {
						$objArea = $sel->fetch_object();
					}
					else if ($objArea->id_area == NULL) {
						$ins_area = "INSERT INTO `interbccs`.`area_proj` (`id_area`, `id_projeto`) VALUE ('".$value."', '".$id."')";
						$conn->query($ins_area);				
					}
				}

			//Se ainda restar tuplas checked para ser removidas
			while ($objArea->id_area != NULL) {
				$del_area = "DELETE FROM `interbccs`.`area_proj` WHERE id_area =".$objArea->id_area." AND id_projeto =".$id;
				$conn->query($del_area);
				$objArea = $sel->fetch_object();
			}
		}
			
			//Update dos dados dos professores
			$selProf = "SELECT `id_professor`
			FROM `proj_prof`
			WHERE `id_projeto` ='".$id."' AND `id_projeto` !=".$_SESSION['idSave'];

			$sel = $conn->query($selProf);
			$objProf = $sel->fetch_object();
		
//			if (count($_POST['checkprof']) != NULL) {
				foreach ($_POST['checkprof'] as $key => $val) {
					for (;$objProf->id_professor != NULL && $objProf->id_professor < $val;) {
						//Remove a tupla
						if ($objProf->id_professor != $_SESSION['idSave']) {
							$del_prof = "DELETE FROM `interbccs`.`proj_prof` WHERE id_professor =".$objProf->id_professor." AND id_projeto =".$id;
							$conn->query($del_prof);							
						}	
						//Incrementa o obj
						if ($objProf->id_professor != NULL) {
							$objProf = $sel->fetch_object();
						}
					}
					//Verifica se o id encontrado precisa ser inserido
					if ($objProf->id_professor != NULL && $val < $objProf->id_professor) {
						$ins_prof = "INSERT INTO `interbccs`.`proj_prof` (`id_professor`, `id_projeto`) VALUE ('".$val."', '".$id."')";
						$conn->query($ins_prof);
					}
					else if ($objProf->id_professor != NULL && $objProf->id_professor == $val) {
						$objProf = $sel->fetch_object();
					}
					else if ($objProf->id_professor == NULL) {
						$ins_prof = "INSERT INTO `interbccs`.`proj_prof` (`id_professor`, `id_projeto`) VALUE ('".$val."', '".$id."')";
						$conn->query($ins_prof);				
					}
				}

				//Se ainda restar tuplas checked para ser removidas
				while ($objProf->id_professor != NULL) {
					if ($objProf->id_professor != $_SESSION['idSave']) {						
					$del_prof = "DELETE FROM `interbccs`.`proj_prof` WHERE id_professor =".$objProf->id_professor." AND id_projeto =".$id;
					$conn->query($del_prof);
					}
					$objProf = $sel->fetch_object();
				}
//			}
			$_SESSION['mensagem'] = "Atualização feita com sucesso!";
			header('Location: ./home.php');
	}

?>
<!DOCTYPE html>
<html lang="pt-BR">
    <footer>
    </footer>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta charset="utf-8">
				<meta name="author" content="Lucas Penteado Sacchi">
				<meta name="author" content="Sofia de Almeida Machado da Silveira">			
        <title>InterBCCS</title>
        <link rel="shortcut icon" type="image/png" href="../Imagens/Inter%20BCCS%20Logo%20Fundo%20Branco.png">
        
        <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
				<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <link rel="stylesheet" href="../js/bootstrap.min.js">
				<link rel="stylesheet" href="../css/navbarfooter.css">
				<link rel="stylesheet" href="../css/cadStyle.css">
				<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    </head>
    <body>

			<nav class="navbar navbar-expand-md my-nav">
				<div class="d-flex flex-row justify-content-between col-md-12">
					<div class="d-flex col-2">
						<a class="nav-item" href="home.php">
								<img src="../Imagens/Inter%20BCCS%20Logo%20Fundo%20Branco.png" width="50px" height="50px" alt="logo">
						</a>
					</div>

					<div class="d-flex justify-content-center">
						<h4>INTERBCCS</h4>
					</div>

					<div class="d-flex flex-row flex-nowrap col-2 justify-content-end">
						<a class="nav-link" href="./perfil.php">PERFIL</a>
						<a class="nav-link" href="./sair.php">SAIR</a>
					</div>
				</div>
			</nav>

        <br><br>
				<div class="container">

					<form name="form" class="form-horizontal" action="./alterar_cadastro.php?id=<?php echo $id;?>" method="post" enctype="multipart/form-data">
						<div class="text-center">
								<h2>Alterar projeto</h2>
								<p>Altere os dados dos campos desejados</p>
						</div>
						<div class="row">
							<div class="col-7">
								<div class="form-group">
									<label for="usr">NOME<span class="ast">*</span></label>
									<input type="text" class="form-control" id="nome" name="nome" maxlength="150" value="<?php echo $vetor->nome;?>" required placeholder="Insira o nome do projeto">
								</div>
							</div>
							<div class="col-5 vertical-line">
								<div class="form-group">
									<label for="usr">LINK DO PROJETO</label>
									<input type="text" class="form-control" id="site" name="site" maxlength="150" value="<?php echo $vetor->site_proj;?>" placeholder="Insira a url (opcional)">
								</div>
							</div>
						</div>
						<hr>							
						<div class="form-group">
							<label for="comment">DESCRIÇÃO<span class="ast">*</span></label>
							<textarea type="text" name="descricao" class="form-control" rows="3" required maxlength="1000" id="description" placeholder="Insira a descrição do projeto"><?php echo $vetor->descricao;?></textarea>
						</div>
						<hr>
						
						<div class="row">
							<div class="col-6">
								<label>ÁREAS<span class="ast">*</span></label>
							<div class="form-group">
							<?php
									//Encontra as areas de cada projeto
									$scriptArea = "SELECT id_area, nome
																FROM area";
							
									$res_area = $conn->query($scriptArea);
							
									while ($obj_area=$res_area->fetch_object()) {
							?>
										<input type="checkbox" name="checkarea[]" value="<?php echo $obj_area->id_area;?>"
										<?php
											$script = "SELECT * FROM area NATURAL JOIN area_proj WHERE area_proj.id_area =".$obj_area->id_area." AND area_proj.id_projeto =".$id;
											$sql = $conn->query($script);
											if ($sql->fetch_object()) {
												echo 'checked';
											}
										?>
										>
										<label><?php echo $obj_area->nome;?></label>
							<?php
										echo '<br>';
									}
							?>
							</div>
							</div>
								<div class="col-6 vertical-line">
									<label>PROFESSORES</label>
									<div class="form-group">
									<?php
											//Encontra as areas de cada projeto
											$scriptProf = "SELECT id_professor, nome
																		FROM professor";

											$res_prof = $conn->query($scriptProf);

											while ($obj_prof=$res_prof->fetch_object()) {
									?>
												<input type="checkbox" name="checkprof[]" value="<?php echo $obj_prof->id_professor;?>"
												<?php
													$script = "SELECT * FROM professor NATURAL JOIN proj_prof WHERE proj_prof.id_professor =".$obj_prof->id_professor." AND proj_prof.id_projeto =".$id;
													$sql = $conn->query($script);
													if ($sql->fetch_object()) {
														echo 'checked';
													}
												if($_SESSION['idSave']==$obj_prof->id_professor) echo " disabled"; ?>
												>
												<label><?php echo $obj_prof->nome;?></label>
									<?php
												echo '<br>';
											}
									?>
									</div>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-3">
								<div class="form-group">
									<label>ANO DE INÍCIO DO PROJETO<span class="ast">*</span></label>
									<input type="text" id="data" name="data" pattern="{0,9}[4]" value="<?php echo $vetor->data_inicio;?>" placeholder="AAAA" maxlength="4" size="5" style="text-align:center;" required>
								</div>
							</div>
							<div class="col-3 vertical-line">
								<div class="form-group">
									<label>STATUS DO PROJETO<span class="ast">*</span></label><br>
									<input type="radio" id="andamento" name="andamento" <?php if ($vetor->concluido == false) echo 'checked';?> value="0">
									<label> Em andamento</label><br>
									<input type="radio" id="concluido" name="andamento" <?php if ($vetor->concluido == true) echo 'checked';?> value="1">
									<label> Concluído</label>
								</div>
							</div>
							<div class="col-6 vertical-line">
								<center>
									<div class="form-group">
										<img id="photo" src="../Imagens/<?php echo $vetor->foto;?>" class="img-rounded" width="280" height="210">
										<br>
										<label for="comment">FOTO DO PROJETO<span class="ast">*</span> </label>
										<input type="file" name="file" id="file">
                    <div class="form-group">
                        <input type="hidden" id="photo_change" name="photo_change">
                    </div>										
									</div>
								</center>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-12">
								<div class="form-group">
									<label for="comment">ALUNOS PARTICIPANTES</label>
									<textarea type="text" name="alunos" class="form-control" rows="2" maxlength="200" id="alunos" placeholder="Insira os nomes dos alunos participantes"><?php echo $vetor->alunos;?></textarea>
								</div>
							</div>
						</div>
						<h6><span class="ast">* Campos obrigatórios</span></h6>
						<hr>						
						<div class="d-flex flex-row justify-content-between col-12">
								<button class="btn btn-danger" name="remover_dados">Remover</button>
								<button class="btn btn-secondary" name="salvar_dados">Salvar</button>
						</div>
					</form>
				</div>
        <br><br>

			<footer>
				<div class="footertexto">
           <div class="foot" align="center">© 2018 InterBCCS. All rights reserved.</div>
				</div>
			</footer>
	</body>
	
    <script>
        $(document).ready(function (e) {
            // Function to preview image after validation
            $(function () {
                $("#file").change(function () {
                    var file = this.files[0];
                    var imagefile = file.type;
                    var match = ["image/jpeg", "image/png", "image/jpg"];
                    if (!((imagefile == match[0]) || (imagefile == match[1]) || (imagefile == match[2])))
                    {
                        $('#photo').attr('src', 'noimage.png');
                        $("#message").html("<p id='error'>Please Select A valid Image File</p>" + "<h4>Note</h4>" + "<span id='error_message'>Only jpeg, jpg and png Images type allowed</span>");
                        return false;
                    }
                    else
                    {
                        var reader = new FileReader();
                        reader.onload = imageIsLoaded;
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            });
            function imageIsLoaded(e) {
							$('#photo').attr('src', e.target.result);
							$('#photo').attr('width', '330px');
							$('#photo').attr('height', '210px');
							$('#photo_change').attr('value', 'true');
            }

        });
    </script>
	
		<!-- Trigger para validar as entradas do teclado -->
		<script>
			//Campo nome
			var x = document.getElementById('nome');
			var y = document.getElementById('site');
			var z = document.getElementById('data');
			
			x.addEventListener("keydown",
			function(e) {
				//Verifica se o evento foi um enter
				if (e.keyCode == 13) {
					e.preventDefault();
					document.getElementById('site').focus();
				}
			}
			);
			
			y.addEventListener("keydown",
			function(e) {
				//Verifica se o evento foi um enter
				if (e.keyCode == 13) {
					e.preventDefault();
					document.getElementById('description').focus();
				}
			}
			);

			z.addEventListener("keydown",
			function(e) {
				//Verifica se o evento foi um enter
				if (e.keyCode == 13) {
					e.preventDefault();
					document.getElementById('andamento').focus();
				}
			}
			);			
		</script>
</html>