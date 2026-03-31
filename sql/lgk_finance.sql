CREATE DATABASE IF NOT EXISTS lgk_finance CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lgk_finance;

DROP TABLE IF EXISTS logs, anexos, parcelamentos, recorrencias, contas_receber, contas_pagar, responsaveis, categorias, usuarios;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha VARCHAR(255) NOT NULL,
  tipo_usuario ENUM('Administrador','Usuário') NOT NULL DEFAULT 'Usuário',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categorias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(80) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE responsaveis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  contato VARCHAR(120) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE contas_pagar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(180) NOT NULL,
  categoria_id INT,
  responsavel_id INT,
  usuario_lancou_id INT,
  usuario_pagou_id INT NULL,
  valor DECIMAL(12,2) NOT NULL,
  data_vencimento DATE NOT NULL,
  data_pagamento DATE NULL,
  status ENUM('Pendente','Pago','Vencido','Cancelado','Parcelado') NOT NULL DEFAULT 'Pendente',
  observacoes TEXT NULL,
  recorrente TINYINT(1) NOT NULL DEFAULT 0,
  anexo VARCHAR(255) NULL,
  tipo_pagamento VARCHAR(80) NULL,
  numero_parcelas INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cp_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
  CONSTRAINT fk_cp_resp FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE SET NULL,
  CONSTRAINT fk_cp_ul FOREIGN KEY (usuario_lancou_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT fk_cp_up FOREIGN KEY (usuario_pagou_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE contas_receber (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(180) NOT NULL,
  categoria_id INT,
  responsavel_id INT,
  valor DECIMAL(12,2) NOT NULL,
  data_prevista DATE NOT NULL,
  data_recebimento DATE NULL,
  status ENUM('Pendente','Recebido','Vencido','Cancelado') NOT NULL DEFAULT 'Pendente',
  observacoes TEXT NULL,
  recorrente TINYINT(1) NOT NULL DEFAULT 0,
  anexo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_cr_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
  CONSTRAINT fk_cr_resp FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE SET NULL
);

CREATE TABLE recorrencias (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('pagar','receber') NOT NULL,
  descricao VARCHAR(180) NOT NULL,
  valor DECIMAL(12,2) NOT NULL,
  categoria_id INT,
  responsavel_id INT,
  periodicidade VARCHAR(30) NOT NULL DEFAULT 'Mensal',
  proxima_data DATE NOT NULL,
  ativo TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_rec_cat FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
  CONSTRAINT fk_rec_resp FOREIGN KEY (responsavel_id) REFERENCES responsaveis(id) ON DELETE SET NULL
);

CREATE TABLE parcelamentos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  descricao VARCHAR(180) NOT NULL,
  valor_total DECIMAL(12,2) NOT NULL,
  quantidade_parcelas INT NOT NULL,
  parcela_atual INT NOT NULL,
  valor_parcela DECIMAL(12,2) NOT NULL,
  saldo_restante DECIMAL(12,2) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'Ativo',
  conta_pagar_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_par_cp FOREIGN KEY (conta_pagar_id) REFERENCES contas_pagar(id) ON DELETE SET NULL
);

CREATE TABLE anexos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tipo ENUM('pagar','receber') NOT NULL,
  referencia_id INT NOT NULL,
  nome_arquivo VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT,
  acao VARCHAR(50) NOT NULL,
  entidade VARCHAR(80) NOT NULL,
  entidade_id INT NULL,
  descricao TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_log_user FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

INSERT INTO usuarios (nome,email,senha,tipo_usuario,status) VALUES
('Administrador LGK','admin@lgkfinance.local','$2y$10$BNCQat7G6O6BJIwM99fM0ugWWJfFb9LFnDvEDfS6xA6vkbYNAtM.O','Administrador',1);

INSERT INTO categorias (nome) VALUES
('Água'),('Luz'),('Internet'),('Mercado'),('Aluguel'),('Cartão'),('Manutenção'),('Saúde'),('Educação'),('Outros');

INSERT INTO responsaveis (nome, contato) VALUES
('Casa - Geral','N/A'),('João','11999990000'),('Maria','11999990001');

INSERT INTO contas_pagar (descricao,categoria_id,responsavel_id,usuario_lancou_id,valor,data_vencimento,status,tipo_pagamento,recorrente) VALUES
('Conta de Luz Março',2,1,1,320.50,CURDATE(),'Pendente','PIX',1),
('Aluguel',5,1,1,1800.00,DATE_ADD(CURDATE(), INTERVAL 3 DAY),'Pendente','Transferência',1),
('Compra Mercado',4,2,1,550.00,DATE_SUB(CURDATE(), INTERVAL 2 DAY),'Vencido','Cartão',0);

INSERT INTO contas_receber (descricao,categoria_id,responsavel_id,valor,data_prevista,status,recorrente) VALUES
('Reembolso condomínio',10,1,300.00,DATE_ADD(CURDATE(), INTERVAL 5 DAY),'Pendente',0),
('Freela mensal',10,2,2500.00,CURDATE(),'Recebido',1);

INSERT INTO recorrencias (tipo,descricao,valor,categoria_id,responsavel_id,periodicidade,proxima_data,ativo) VALUES
('pagar','Internet Fibra',120.00,3,1,'Mensal',DATE_ADD(CURDATE(), INTERVAL 10 DAY),1),
('receber','Aluguel vaga garagem',400.00,10,1,'Mensal',DATE_ADD(CURDATE(), INTERVAL 7 DAY),1);

INSERT INTO parcelamentos (descricao,valor_total,quantidade_parcelas,parcela_atual,valor_parcela,saldo_restante,status) VALUES
('Geladeira nova',3600.00,12,4,300.00,2400.00,'Ativo');
