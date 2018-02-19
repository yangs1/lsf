## 简单介绍
   这是一个利用 lumen 组件 + swoole 的 http server 重新组合的 php 框架。
   
   本框架保留了 lumen 原始结构，保留了 lumen 常用的组件，保留 lumen IOC 服务容器的模式，以便其他组件/扩展的添加。
   
## 简单用法
        php server.php start | reload | stop | status
        
## 性能测试

 
        ab -n 1000 -c 100 http://127.0.0.1:9501/
        
        Concurrency Level:      100
        Time taken for tests:   0.173 seconds
        Complete requests:      1000
        Failed requests:        0
        Total transferred:      210000 bytes
        HTML transferred:       21000 bytes
        Requests per second:    5783.59 [#/sec] (mean)
        Time per request:       17.290 [ms] (mean)
        Time per request:       0.173 [ms] (mean, across all concurrent requests)
        Transfer rate:          1186.09 [Kbytes/sec] received

       
  