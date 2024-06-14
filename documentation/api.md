```mermaid
graph BT;
 subgraph Api
        api(ApiPlatform)
    end

    subgraph Provider
        provider(UsersStatisticsProvider)
    end

    subgraph Entities
        entity1(User)
        entity2(Win)
        entity3(Deposit)
    end

    subgraph Repositories
        repository1(UserRepository)
        repository2(WinRepository)
        repository3(DepositRepository)
    end

    subgraph DataBase
        id1[(Database)]
    end

   


    Repositories -- normalize --> Entities

    Repositories -- sends queries to --> DataBase
    DataBase -- returns results to --> Repositories

    Entities --> provider

    provider -- Http Response --> Api

    Api -. Http Request (Get, Post, Put, Patch, Delete) .-> Repositories

    classDef redBox fill:#ccccff, stroke:#8080ff, stroke-width:2px, font-weight:bold;
    class Repositories,Entities,Controllers,Services,Templates,Api,Provider redBox;
