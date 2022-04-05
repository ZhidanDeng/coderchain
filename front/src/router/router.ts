import Vue from 'vue'
import Router from 'vue-router'
import Home from '../views/home/Home.vue'

Vue.use(Router)

export default new Router({
  mode: 'hash',
  base: process.env.BASE_URL,
  routes: [
    {
      path: '/',
      name: 'home',
      component: Home,
      meta: {
        title: 'Welcome to CoderChain - A Developer Community'
      }
    },
    // {
    //   path: '/about',
    //   name: 'about',
    //   // route level code-splitting
    //   // this generates a separate chunk (about.[hash].js) for this route
    //   // which is lazy-loaded when the route is visited.
    //   component: () =>
    //     import(/* webpackChunkName: "about" */ '../views/About.vue')
    // },
    {
      path: '/project',
      name: 'project',
      meta: {
        keepAlive: true,
        title: '发现有趣的项目 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "project" */ '../views/project/Project.vue')
    },
    {
      path: '/project/create',
      name: 'create-project',
      meta: {
        title: '创建项目 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "createProject" */ '../views/project/CreateProject.vue')
    },
    {
      path: '/feedback',
      name: 'feedback',
      meta: {
        title: '意见反馈 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "feedback" */ '../views/feedback/Feedback.vue')
    },
    {
      path: '/preview',
      name: 'preview',
      meta: {
        title: '文件预览 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "preview" */ '../views/preview/Preview.vue')
    },
    {
      path: '/about',
      name: 'about',
      meta: {
        title: '关于 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "about" */ '../views/about/About.vue')
    },
    {
      path: '/chart',
      name: 'chart',
      meta: {
        title: '统计 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "chart" */ '../views/chart/Chart.vue')
    },
    {
      path: '/rank',
      name: 'rank',
      meta: {
        keepAlive: true,
        title: '项目排行 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "rank" */ '../views/rank/Rank.vue')
    },
    {
      path: '/setting/profile',
      name: 'setting-profile',
      meta: {
        keepAlive: true,
        title: '个人设置 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "setting-profile" */ '../views/setting/Profile.vue')
    },
    {
      path: '/transfer',
      name: 'transfer',
      meta: {
        // keepAlive: true,
        title: '在线转账 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "setting-profile" */ '../views/tx/Transfer.vue')
    },
    {
      path: '/tx',
      name: 'tx',
      meta: {
        keepAlive: true,
        title: '交易详情 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "setting-profile" */ '../views/tx/TxList.vue')
    },
    {
      path: '/setting/password',
      name: 'setting-password',
      meta: {
        keepAlive: true,
        title: '修改密码 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "setting-password" */ '../views/setting/Password.vue')
    },
    {
      path: '/user/:userName',
      name: 'user-home',
      meta: {
        title: '个人主页 - CoderChain'
      },
      component: () =>
        import(/* webpackChunkName: "user-home" */ '../views/user-home/UserHome.vue'),
      redirect: '/user/:userName/project',
      children: [
        {
          path: 'project',
          name: 'user-project',
          meta: {
            title: '个人主页 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "user-project" */ '../views/user-home/UserProject.vue')
        },
        {
          path: 'fromTx',
          name: 'user-fromTx',
          meta: {
            title: '个人主页 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "user-support" */ '../views/user-home/UserFromTx.vue')
        },
        {
          path: 'toTx',
          name: 'user-toTx',
          meta: {
            title: '个人主页 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "user-support" */ '../views/user-home/UserToTx.vue')
        }
      ]
    },
    {
      path: '/detail',
      name: 'project-detail',
      component: () =>
        import(/* webpackChunkName: "project-detail" */ '../views/detail/DetailIndex.vue'),
      children: [
        // {
        //   path: 'more',
        //   name: 'detail-more',
        //   meta: {
        //     title: '更多操作 - CoderChain'
        //   },
        //   component: () =>
        //     import(/* webpackChunkName: "detail-code"*/ '../views/detail/DetailMore.vue')
        // },
        {
          path: 'code',
          name: 'detail-code',
          meta: {
            title: '项目详情 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "detail-code"*/ '../views/detail/DetailCode.vue')
        },
        // {
        //   path: 'report',
        //   name: 'detail-report',
        //   meta: {
        //     title: '项目报告 - CoderChain'
        //   },
        //   component: () =>
        //     import(/* webpackChunkName: "detail-report"*/ '../views/detail/DetailReport.vue')
        // },
        {
          path: 'vote',
          name: 'detail-vote',
          meta: {
            title: '项目投票详情 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "detail-vote"*/ '../views/detail/DetailVote.vue')
        },
        {
          path: 'setting',
          name: 'detail-setting',
          meta: {
            title: '项目设置 - CoderChain'
          },
          component: () =>
            import(/* webpackChunkName: "detail-setting"*/ '../views/detail/DetailSetting.vue')
        }
      ]
    },
    // {
    //   path: '/issues',
    //   name: 'issues',
    //   meta: {
    //     keepAlive: true,
    //     title: 'issues - CoderChain'
    //   },
    //   component: () =>
    //     import(/* webpackChunkName: "issues" */ '../views/issues/IssuesIndex.vue'),
    //     children: [
    //       {
    //         path: 'list',
    //         name: 'issues-list',
    //         meta: {
    //           title: 'issues列表 - CoderChain'
    //         },
    //         component: () =>
    //           import('../views/issues/IssuesList.vue'),
    //           children: [
    //             {
    //               path: 'all',
    //               name: 'issues-all',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             },
    //             {
    //               path: 'refuse',
    //               name: 'issues-refuse',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             },
    //             {
    //               path: 'todo',
    //               name: 'issues-todo',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             },
    //             {
    //               path: 'ongoing',
    //               name: 'issues-ongoing',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             },
    //             {
    //               path: 'completed',
    //               name: 'issues-completed',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             },
    //             {
    //               path: 'open',
    //               name: 'issues-open',
    //               meta: {
    //                 title: 'issues列表 - CoderChain'
    //               },
    //               component: () =>
    //                 import('../views/issues/IssuesTable.vue')
    //             }
    //           ]
    //       }
    //     ]
    // },
    // {
    //   path: '/issues/detail',
    //   name: 'issues-detail',
    //   meta: {
    //     title: 'issues信息 - CoderChain'
    //   },
    //   component: () =>
    //     import(/* webpackChunkName: "detail-code"*/ '../views/issues/IssuesDetail.vue')
    // },
    // {
    //   path: '/issues/add',
    //   name: 'issues-add',
    //   meta: {
    //     title: '新增issues - CoderChain'
    //   },
    //   component: () =>
    //     import(/* webpackChunkName: "detail-code"*/ '../views/issues/IssuesAdd.vue')
    // }
  ]
})
