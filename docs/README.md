# memo

Laravel 12、Livewire 4、tailwind 4、AWSを試すためのちょっとしたメモアプリのようなもの。

![Image](https://github.com/user-attachments/assets/190d1afe-d824-4673-a09d-e13528e6aa51)


## AWS構成図
<img width="791" height="1011" alt="Image" src="https://github.com/user-attachments/assets/0a529878-ff01-48c4-bd81-f62c1f69bfd4" /><br/>
DBはEFS内にsqliteで保持する想定。<br/>
セッションはlaravelでDBセッションにする想定。

節約のためにNAT Gatewayは1az。<br/>
AZ間通信料がかかるが、ECRのイメージpullでしか使わない想定。<br/>
本当にイメージpullだけならVPCエンドポイントを作ることでも可能だが、各azに2,3個作ることになりそこまで安くないので、シンプルにNAT Gatewayにした。<br/>
ALBとNAT Gatewayは常に料金がかかるので、使う時だけcloudformationで作成する想定。
