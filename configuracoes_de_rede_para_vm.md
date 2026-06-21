# Esse guia foi feito com o auxílio de inteligência artificial para um melhor entendimento do projeto e de como escalar ele para o ambiente em que será apresentado.


# Configurações de Redes e Sistemas Operacionais - Projeto Acadêmico

Este guia descreve o passo a passo para configurar o ambiente de infraestrutura de Redes e Sistemas Operacionais exigido na rubrica de avaliação do projeto **Barbearia Prime**.

As configurações descritas a seguir deverão ser realizadas na **Máquina Virtual (VM)** de testes ou na máquina definitiva de apresentação.

---

## 1. Configuração de IP Fixo na Máquina Virtual
Para garantir que a aplicação esteja acessível por um endereço persistente, a VM que hospeda o servidor Apache deve possuir um IP Fixo (estático).

### 1.1. No Windows (Painel de Controle)
1. Abra as **Conexões de Rede** (`control netconnections`).
2. Clique com o botão direito na interface de rede ativa (ex: Ethernet) e selecione **Propriedades**.
3. Selecione **Protocolo IP Versão 4 (TCP/IPv4)** e clique em **Propriedades**.
4. Selecione a opção **"Usar o seguinte endereço IP"**.
5. Preencha os campos com os valores da sua sub-rede local (exemplo):
   *   **Endereço IP**: `192.168.56.101` (ou um IP livre na faixa da VM)
   *   **Máscara de sub-rede**: `255.255.255.0`
   *   **Gateway padrão**: IP da máquina física ou do roteador (ex: `192.168.56.1`)
6. Defina os servidores DNS (ex: `8.8.8.8` e `8.8.4.4`) e salve.

### 1.2. No Linux (Ubuntu Server - Netplan)
1. Edite o arquivo de rede em `/etc/netplan/`:
   ```bash
   sudo nano /etc/netplan/00-installer-config.yaml
   ```
2. Configure a interface para IP estático (exemplo):
   ```yaml
   network:
     ethernets:
       enp0s3:
         dhcp4: no
         addresses:
           - 192.168.56.101/24
         gateway4: 192.168.56.1
         nameservers:
           addresses: [8.8.8.8, 8.8.4.4]
     version: 2
   ```
3. Aplique as configurações:
   ```bash
   sudo netplan apply
   ```

---

## 2. Configuração do Apache na Porta 8080
A rubrica exige que a aplicação funcione na porta **8080** em vez da porta padrão 80.

### 2.1. No Apache do XAMPP:
1. Abra o painel do XAMPP e clique no botão **Config** ao lado de Apache, escolhendo **Apache (httpd.conf)**.
2. Busque pela linha que define a porta padrão de escuta (aproximadamente na linha 45):
   ```apache
   # De:
   Listen 80
   
   # Altere para:
   Listen 8080
   ```
3. Busque pela diretiva `ServerName` (aproximadamente na linha 225) e atualize a porta:
   ```apache
   # De:
   ServerName localhost:80
   
   # Altere para:
   ServerName localhost:8080
   ```
4. Salve o arquivo e reinicie o servidor Apache no painel do XAMPP.
5. Agora, o site será acessado localmente pelo endereço: `http://localhost:8080/barbearia/public/`.

---

## 3. Configuração de DNS Local para a Aplicação
Para acessar a aplicação utilizando um nome de domínio personalizado (ex: `barbearia.local`) em vez do IP puro, configure o DNS local.

### 3.1. Configuração na Máquina Cliente (Física/Hospedeira):
1. Abra o terminal (Prompt de Comando ou PowerShell) como **Administrador**.
2. Abra o arquivo `hosts` do Windows:
   ```powershell
   notepad C:\Windows\System32\drivers\etc\hosts
   ```
   *(No Linux/macOS, o arquivo fica em `/etc/hosts` e deve ser editado com `sudo nano /etc/hosts`).*
3. Adicione uma linha ao final mapeando o IP fixo da VM para o domínio escolhido:
   ```text
   192.168.56.101     barbearia.local
   ```
4. Salve e feche o arquivo.
5. Agora, na máquina física, você poderá acessar o site pelo navegador usando: `http://barbearia.local:8080/barbearia/public/`.

---

## 4. Separação de Aplicação e Banco de Dados em Duas Máquinas (VMs)
A rubrica avalia se a aplicação e o banco de dados rodam em servidores físicos ou virtuais independentes.

### 4.1. Configuração do Banco de Dados (VM 1 - IP Fixo ex: `192.168.56.102`):
1. **Permitir Conexão Externa no MySQL (XAMPP)**:
   *   No painel do XAMPP da VM 1, abra a configuração do MySQL (`my.ini`).
   *   Certifique-se de que a diretiva `bind-address` esteja desativada ou configurada para escutar em qualquer rede:
       ```ini
       bind-address = 0.0.0.0
       ```
2. **Criar Usuário de Acesso Remoto**:
   *   Acesse o PHPMyAdmin ou o console do MySQL na VM 1 e execute:
       ```sql
       -- Cria um usuário root que pode se conectar de qualquer IP externo (%)
       CREATE USER 'root'@'%' IDENTIFIED BY '';
       GRANT ALL PRIVILEGES ON barbearia.* TO 'root'@'%' WITH GRANT OPTION;
       FLUSH PRIVILEGES;
       ```

### 4.2. Configuração da Aplicação (VM 2 - IP Fixo ex: `192.168.56.101`):
1. Abra o arquivo de conexão da aplicação em `config/conexao.php`.
2. Altere o servidor local (`localhost`) para o IP fixo da VM 1 (onde está o MySQL):
   ```php
   function conectarBanco(): mysqli
   {
       // Substitua 'localhost' pelo IP Fixo do servidor do banco (VM 1)
       $servidor = '192.168.56.102'; 
       $usuario = 'root';
       $senha = ''; // insira a senha se houver
       $banco = 'barbearia';
       
       $conexao = new mysqli($servidor, $usuario, $senha, $banco);
       ...
   ```
