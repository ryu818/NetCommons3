NetCommons
=======

国立情報学研究所が次世代情報共有基盤システムとして開発しています。サポート情報やライセンスなどの最新の情報は公式サイトを御覧ください。
こちらのリポジトリは最新版として開発中のv3となります。安定版ではありませんのでご注意ください。現在の安定版については[こちらのレポジトリ](https://github.com/netcommons)をご覧ください。

[NetCommons公式サイト](http://www.netcommons.org/)


[![Build Status](https://travis-ci.org/ryu818/NetCommons3.png?branch=develop)](https://travis-ci.org/ryu818/NetCommons3)

# 開発環境での起動

## 事前準備

### VirtualBoxをダウンロードしてインストール
VirtualBoxをセットアップしてください。
[https://www.virtualbox.org/wiki/Downloads](https://www.virtualbox.org/wiki/Downloads)

### Vagrantをダウンロードしてインストール
最新版をインストールしてください。
[http://downloads.vagrantup.com/](http://downloads.vagrantup.com/)

### このリポジトリをクローン
gitでクローンするか、ZIPなどでダウンロードしてください。
<pre>
git clone https://github.com/ryu818/NetCommons3.git
</pre>

## 起動

### vagrantを起動
クローンしたリポジトリ内でvagrantを起動。初回のみOSのダウンロードに時間がかかります。
<pre>
cd NetCommons3
vagrant up
</pre>

### 動作確認
http://127.0.0.1:8080 にアクセスする。IPアドレスやポート番号を変更する場合はVagrantfileを編集します。
またサーバ内にSSHしたい場合はvagrantコマンドを使います。(ホストOSがWindowsの場合はPuttyなどで127.0.0.1の2020に自分でsshしてください。)

<pre>
vagrant ssh
</pre>

インストーラーではDBのユーザ名はroot、パスワードは無しで進めます。

### 終了
vagrantコマンドで仮想マシンを終了、又は破棄出来ます。

一旦止めるだけの場合。
<pre>
vagrant halt
</pre>

データを破棄する場合。次回、<code>vagrant up</code>の際にはまっさらなマシンから新規インストールが行われる。
<pre>
vagrant destroy
</pre>