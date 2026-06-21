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