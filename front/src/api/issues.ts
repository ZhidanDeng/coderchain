import fetch from '@utils/request'



export const getIssuesList = (
    suserName: string,
    sprojectName: string,
    sstatus: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/getIssuesList',
        data: {
            userName: suserName,
            projectName: sprojectName,
            status: sstatus

        }
    })
}

export const addIssues = (
    sprojectName: string,
    suserName: string,
    stitle: string,
    scontent: string,
    stype: number,
    sstatus: number,
    slevel: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/addIssues',
        data: {
            userName: suserName,
            projectName: sprojectName,
            title: stitle,
            content: scontent,
            type: stype,
            status: sstatus,
            level: slevel
        }
    })
}

export const getIssuesInfo = (
    sprojectName: string,
    suserName: string,
    sissueID: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/getIssuesInfo',
        data: {
            userName: suserName,
            projectName: sprojectName,
            issuesID: sissueID
        }
    })
}

export const editIssues = (
    sprojectName: string,
    suserName: string,
    stitle: string,
    scontent: string,
    stype: number,
    sstatus: number,
    slevel: number,
    sissueID: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/editIssues',
        data: {
            userName: suserName,
            projectName: sprojectName,
            title: stitle,
            content: scontent,
            type: stype,
            status: sstatus,
            level: slevel,
            issueID: sissueID
        }
    })
}

export const updateIssuesStatus = (data: FormData): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/updateIssuesStatus',
        data: data,
        // formData格式的数据不转换成表单格式
        transformRequest: [
            function (data) {
                return data
            }
        ]
    })
}

export const batchUpdateIssuesStatus = (
    sprojectName: string,
    suserName: string,
    sstatus: number,
    sissueID: string
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/batchUpdateIssuesStatus',
        data: {
            userName: suserName,
            projectName: sprojectName,
            status: sstatus,
            issueID: sissueID
        }
    })
}


export const deleteIssues = (
    sprojectName: string,
    suserName: string,
    sissueID: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/deleteIssues',
        data: {
            userName: suserName,
            projectName: sprojectName,
            issueID: sissueID
        }
    })
}

export const batchDeleteIssues = (
    sprojectName: string,
    suserName: string,
    sissueID: string
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/batchDeleteIssues',
        data: {
            userName: suserName,
            projectName: sprojectName,
            issueID: sissueID
        }
    })
}

export const addComment = (
    scontent: string,
    sissueID: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/addComment',
        data: {
            content: scontent,
            issueID: sissueID
        }
    })
}

export const editComment = (data: FormData): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/editComment',
        data: data,
        // formData格式的数据不转换成表单格式
        transformRequest: [
            function (data) {
                return data
            }
        ]
    })
}

export const deleteComment = (
    sprojectName: string,
    suserName: string,
    sissueID: number,
    scommentID: number
): Promise<any> => {
    return fetch({
        method: 'post',
        url: '/issues/Issues/deleteComment',
        data: {
            userName: suserName,
            projectName: sprojectName,
            issueID: sissueID,
            commentID: scommentID
        }
    })
}



