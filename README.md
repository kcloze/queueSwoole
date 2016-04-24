##queueSwoole


##目标
* 基于swoole实现排队抢购系统，适用于高并发场景
* 对性能要求极高，qps至少同配置php-fpm一倍以上

##难点
* 库存控制
* 排队公平性
* 稳定性

##设计文档
* [架构图](https://github.com/kcloze/queueSwoole/blob/master/project.md)


##启动
```
chmod u+x server.sh
./server.sh start|stop

```

##压测




##感谢

