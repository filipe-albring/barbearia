CREATE TABLE `Cliente`(
    `id_cliente` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nm_cliente` VARCHAR(100) NOT NULL,
    `dt_nascimento` DATE NOT NULL,
    `cpf_cliente` VARCHAR(14) NOT NULL
);
ALTER TABLE
    `Cliente` ADD UNIQUE `cliente_cpf_cliente_unique`(`cpf_cliente`);
CREATE TABLE `Cliente_Telefone`(
    `id_telefone` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nr_telefone` VARCHAR(15) NOT NULL,
    `id_cliente` INT UNSIGNED NOT NULL
);
CREATE TABLE `Cliente_Email`(
    `id_email` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nm_email` VARCHAR(100) NOT NULL,
    `id_cliente` INT UNSIGNED NOT NULL
);
ALTER TABLE
    `Cliente_Email` ADD UNIQUE `cliente_email_nm_email_unique`(`nm_email`);
CREATE TABLE `Barbeiro`(
    `id_barbeiro` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nm_barbeiro` VARCHAR(100) NOT NULL,
    `cpf_barbeiro` VARCHAR(14) NOT NULL
);
ALTER TABLE
    `Barbeiro` ADD UNIQUE `barbeiro_cpf_barbeiro_unique`(`cpf_barbeiro`);
CREATE TABLE `Barbeiro_Telefone`(
    `id_telefone` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nr_telefone` VARCHAR(15) NOT NULL,
    `id_barbeiro` INT UNSIGNED NOT NULL
);
CREATE TABLE `Barbeiro_Email`(
    `id_email` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nm_email` VARCHAR(100) NOT NULL,
    `id_barbeiro` INT UNSIGNED NOT NULL
);
CREATE TABLE `Servico`(
    `id_servico` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `nm_servico` VARCHAR(20) NOT NULL,
    `vl_preco` DECIMAL(10, 2) NOT NULL
);
CREATE TABLE `Agendamento`(
    `id_agendamento` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `dt_hora` DATETIME NOT NULL,
    `status` VARCHAR(20) NOT NULL,
    `id_cliente` INT UNSIGNED NOT NULL,
    `id_barbeiro` INT UNSIGNED NOT NULL
);
CREATE TABLE `Agendamento_Servico`(
    `id_agendamento` INT UNSIGNED NOT NULL,
    `id_servico` INT UNSIGNED NOT NULL,
    PRIMARY KEY(`id_agendamento`, `id_servico`)
);
ALTER TABLE
    `Barbeiro_Email` ADD CONSTRAINT `barbeiro_email_id_barbeiro_foreign` FOREIGN KEY(`id_barbeiro`) REFERENCES `Barbeiro`(`id_barbeiro`);
ALTER TABLE
    `Agendamento_Servico` ADD CONSTRAINT `agendamento_servico_id_agendamento_foreign` FOREIGN KEY(`id_agendamento`) REFERENCES `Agendamento`(`id_agendamento`);
ALTER TABLE
    `Cliente_Email` ADD CONSTRAINT `cliente_email_id_cliente_foreign` FOREIGN KEY(`id_cliente`) REFERENCES `Cliente`(`id_cliente`);
ALTER TABLE
    `Agendamento_Servico` ADD CONSTRAINT `agendamento_servico_id_servico_foreign` FOREIGN KEY(`id_servico`) REFERENCES `Servico`(`id_servico`);
ALTER TABLE
    `Barbeiro_Telefone` ADD CONSTRAINT `barbeiro_telefone_id_barbeiro_foreign` FOREIGN KEY(`id_barbeiro`) REFERENCES `Barbeiro`(`id_barbeiro`);
ALTER TABLE
    `Agendamento` ADD CONSTRAINT `agendamento_id_cliente_foreign` FOREIGN KEY(`id_cliente`) REFERENCES `Cliente`(`id_cliente`);
ALTER TABLE
    `Agendamento` ADD CONSTRAINT `agendamento_id_barbeiro_foreign` FOREIGN KEY(`id_barbeiro`) REFERENCES `Barbeiro`(`id_barbeiro`);
ALTER TABLE
    `Cliente_Telefone` ADD CONSTRAINT `cliente_telefone_id_cliente_foreign` FOREIGN KEY(`id_cliente`) REFERENCES `Cliente`(`id_cliente`);

-- Objetos analíticos usados pelo dashboard.
DROP TRIGGER IF EXISTS `trg_servico_preco_positivo_bi`;
DROP TRIGGER IF EXISTS `trg_servico_preco_positivo_bu`;

CREATE TRIGGER `trg_servico_preco_positivo_bi`
BEFORE INSERT ON `Servico`
FOR EACH ROW
    SET NEW.`vl_preco` = CASE
        WHEN NEW.`vl_preco` IS NULL OR NEW.`vl_preco` = 0 THEN 0.01
        ELSE ABS(NEW.`vl_preco`)
    END;

CREATE TRIGGER `trg_servico_preco_positivo_bu`
BEFORE UPDATE ON `Servico`
FOR EACH ROW
    SET NEW.`vl_preco` = CASE
        WHEN NEW.`vl_preco` IS NULL OR NEW.`vl_preco` = 0 THEN 0.01
        ELSE ABS(NEW.`vl_preco`)
    END;

CREATE OR REPLACE VIEW `vw_dashboard_itens_financeiros` AS
SELECT
    a.`id_agendamento`,
    a.`dt_hora`,
    CASE
        WHEN UPPER(TRIM(a.`status`)) IN ('CANCELADO', 'CANCELADA') THEN 'Cancelado'
        WHEN UPPER(TRIM(a.`status`)) IN ('CONCLUIDO', 'CONCLUÍDO', 'FINALIZADO', 'FINALIZADA') THEN 'Concluído'
        WHEN UPPER(TRIM(a.`status`)) = 'AGENDADO' THEN 'Agendado'
        WHEN a.`status` IS NULL OR TRIM(a.`status`) = '' THEN 'Pendente'
        ELSE TRIM(a.`status`)
    END AS `status`,
    COALESCE(NULLIF(TRIM(c.`nm_cliente`), ''), 'Cliente não identificado') AS `cliente`,
    COALESCE(NULLIF(TRIM(b.`nm_barbeiro`), ''), 'Barbeiro não informado') AS `barbeiro`,
    s.`id_servico`,
    COALESCE(NULLIF(TRIM(s.`nm_servico`), ''), 'Serviço não informado') AS `servico`,
    CASE
        WHEN s.`vl_preco` IS NULL THEN CAST(0.00 AS DECIMAL(10, 2))
        ELSE GREATEST(ABS(s.`vl_preco`), 0.00)
    END AS `valor_unitario`
FROM `Agendamento` AS a
INNER JOIN `Cliente` AS c ON c.`id_cliente` = a.`id_cliente`
INNER JOIN `Barbeiro` AS b ON b.`id_barbeiro` = a.`id_barbeiro`
LEFT JOIN `Agendamento_Servico` AS ags ON ags.`id_agendamento` = a.`id_agendamento`
LEFT JOIN `Servico` AS s ON s.`id_servico` = ags.`id_servico`;

CREATE OR REPLACE VIEW `vw_dashboard_agendamentos` AS
SELECT
    `id_agendamento`,
    MIN(`dt_hora`) AS `dt_hora`,
    MIN(`status`) AS `status`,
    MIN(`cliente`) AS `cliente`,
    MIN(`barbeiro`) AS `barbeiro`,
    COUNT(DISTINCT `id_servico`) AS `quantidade_servicos`,
    COALESCE(SUM(`valor_unitario`), 0.00) AS `valor_total`,
    GROUP_CONCAT(DISTINCT `servico` ORDER BY `servico` SEPARATOR ', ') AS `servicos`
FROM `vw_dashboard_itens_financeiros`
GROUP BY `id_agendamento`;
