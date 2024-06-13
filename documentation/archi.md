``` mermaid
graph BT;
    subgraph Services
        service1(SecurityService)
    end

    subgraph Controllers
        controller1(AuthController)
        controller2(BalanceController)
        controller3(FirewallPanelController)
        controller4(GameController)
        controller5(HomeController)
        controller6(ProfilController)
    end
    
    subgraph Templates
        template1(add-balance)
        template2(withdraw-balance)
        template3(FirewallPanel)
        template4(game)
        template5(profil)
        template6(register)
        template7(login)
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

    Controllers -- give values --> Templates
    Templates -. return values(optional) .-> Controllers

    Controllers -- call --> Services
    Services -- return values --> Controllers

    Services -- call --> Repositories
    Repositories -- reurn values --> Services

    Repositories -- manage --> Entities

    Repositories -- sends queries to --> DataBase
    DataBase -- returns results to --> Repositories

    classDef redBox fill:#ccccff, stroke:#8080ff, stroke-width:2px, font-weight:bold;
    class DataBase,Repositories,Entities,Controllers,Services,Templates redBox;
```