<template>
  <div class="md-layout">
    <div class="md-layout-item">
      <md-card>
        <md-card-area>
          <md-card-media>
            <img src="../../assets/images/git.png" />
          </md-card-media>

          <md-card-header>
            <div class="md-title">项目拉取</div>
          </md-card-header>

          <md-card-content>您可以选择将码云/GitHub/GitLab的项目部署到coderChain</md-card-content>
        </md-card-area>

        <div>
          <md-dialog :md-active.sync="showGitDialog">
            <md-dialog-title>项目拉取</md-dialog-title>

            <md-tabs md-dynamic-height>
              <md-tab md-label="Gitee" @click="selectGitType = 'gitee'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>项目主人*</label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库名*</label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的空间名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>项目目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择项目的部署目录</p>
                </div>
                <md-dialog-actions>
                  <md-button class="md-primary" @click="onPull">Pull</md-button>
                  <md-button class="md-primary" @click="showGitDialog = false">Close</md-button>
                </md-dialog-actions>
              </md-tab>

              <md-tab md-label="GitHub" @click="selectGitType = 'github'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>github用户名*</label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入github用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库名*</label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建的github仓库名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>项目目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择项目的部署目录</p>
                </div>

                <md-dialog-actions>
                  <md-button class="md-primary" @click="onPull">Pull</md-button>
                  <md-button class="md-primary" @click="showGitDialog = false">Close</md-button>
                </md-dialog-actions>
              </md-tab>

              <md-tab md-label="GitLab" @click="selectGitType = 'gitlab'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>gitlab项目id*</label>
                      <md-input v-model="projectID"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitlab项目时的id</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>项目目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                    
                  </div>
                  <p># 请选择项目的部署目录</p>
                </div>
                <md-dialog-actions>
                  <md-button class="md-primary" @click="onPull">Pull</md-button>
                  <md-button class="md-primary" @click="showGitDialog = false">Close</md-button>
                </md-dialog-actions>
              </md-tab>
            </md-tabs>
          </md-dialog>

          <md-button @click="checkGitDialog">Action</md-button>
        </div>
      </md-card>
    </div>
    <div class="md-layout-item">
      <md-card>
        <md-card-area>
          <md-card-media>
            <img src="../../assets/images/document.png" />
          </md-card-media>

          <md-card-header>
            <div class="md-title">API文档生成</div>
          </md-card-header>

          <md-card-content>基于swagger代码注解，快速生成API文档，依托于coderChain/码云等</md-card-content>
        </md-card-area>
        <div>
          <md-dialog :md-active.sync="showDocDialog">
            <md-dialog-title>API文档生成</md-dialog-title>

            <md-tabs md-dynamic-height>
              <md-tab md-label="coderChain" @click="selectDocType = 'coderChain'">
                <div class="field">
                  <md-field>
                    <label for="repo">目标项目用户名*</label>
                    <md-input v-model="targetUserName"></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <md-field>
                    <label for="repo">目标项目名*</label>
                    <md-input v-model="targetProjectName"></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档名*</label>
                      <md-input v-model="documentName"></md-input>
                    </md-field>
                  </div>
                  <p># 请为生成的文档命名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>生成文档类型*</label>
                      <md-select v-model="documentType">
                        <md-option
                          v-for="dir in selectDocumentType"
                          :value="dir"
                          :key="dir"
                        >{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择保存的文档类型</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>目标项目代码类型*</label>
                      <md-select v-model="targetLanguage">
                        <md-option v-for="dir in selectLanguage" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档保存目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in otherOption" :value="dir" :key="dir">{{ dir }}</md-option>
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
              </md-tab>

              <md-tab md-label="Gitee" @click="selectDocType = 'gitee'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>项目主人*</label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库名*</label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitee仓库的空间名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档名*</label>
                      <md-input v-model="documentName"></md-input>
                    </md-field>
                  </div>
                  <p># 请为生成的文档命名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>生成文档类型*</label>
                      <md-select v-model="documentType">
                        <md-option
                          v-for="dir in selectDocumentType"
                          :value="dir"
                          :key="dir"
                        >{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择保存的文档类型</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>目标项目代码类型*</label>
                      <md-select v-model="targetLanguage">
                        <md-option v-for="dir in selectLanguage" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档保存目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in otherOption" :value="dir" :key="dir">{{ dir }}</md-option>
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
              </md-tab>

              <md-tab md-label="GitHub" @click="selectDocType = 'github'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>github用户名*</label>
                      <md-input v-model="projectOwner"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入github用户名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库名*</label>
                      <md-input v-model="repoName"></md-input>
                    </md-field>
                  </div>
                  <p># 创建的github仓库名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档名*</label>
                      <md-input v-model="documentName"></md-input>
                    </md-field>
                  </div>
                  <p># 请为生成的文档命名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>生成文档类型*</label>
                      <md-select v-model="documentType">
                        <md-option
                          v-for="dir in selectDocumentType"
                          :value="dir"
                          :key="dir"
                        >{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择保存的文档类型</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>目标项目代码类型*</label>
                      <md-select v-model="targetLanguage">
                        <md-option v-for="dir in selectLanguage" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档保存目录*</label>
                      <md-select v-model="projectRoot">
                        <md-option v-for="dir in otherOption" :value="dir" :key="dir">{{ dir }}</md-option>
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
              </md-tab>

              <md-tab md-label="GitLab" @click="selectDocType = 'gitlab'">
                <div class="field">
                  <md-field>
                    <label for="repo">仓库类型*</label>
                    <md-input v-model="selectGitType" disabled></md-input>
                  </md-field>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库地址*</label>
                      <md-input v-model="projectAddress"></md-input>
                    </md-field>
                  </div>
                  <p># 仓库所在的url地址</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>gitlab项目id*</label>
                      <md-input v-model="projectID"></md-input>
                    </md-field>
                  </div>
                  <p># 创建gitlab项目时的id</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>用户私钥*</label>
                      <md-input v-model="projectToken"></md-input>
                    </md-field>
                  </div>
                  <p># 在用户设置中获取访问私钥</p>
                </div>

                <div class="field">
                  <div>
                    <md-field>
                      <label>仓库分支*</label>
                      <md-input v-model="projectRef"></md-input>
                    </md-field>
                  </div>
                  <p># 请输入拉取仓库的分支</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档名*</label>
                      <md-input v-model="documentName"></md-input>
                    </md-field>
                  </div>
                  <p># 请为生成的文档命名</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>生成文档类型*</label>
                      <md-select v-model="documentType">
                        <md-option
                          v-for="dir in selectDocumentType"
                          :value="dir"
                          :key="dir"
                        >{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择保存的文档类型</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>目标项目代码类型*</label>
                      <md-select v-model="targetLanguage">
                        <md-option v-for="dir in selectLanguage" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
                <div class="field">
                  <div>
                    <md-field>
                      <label>文档保存目录*</label>
                      <md-select v-model="projectRoot">
                        
                        <md-option v-for="dir in otherOption" :value="dir" :key="dir">{{ dir }}</md-option>
                        
                        <md-option v-for="dir in projectdDirList" :value="dir" :key="dir">{{ dir }}</md-option>
                      </md-select>
                    </md-field>
                  </div>
                  <p># 请选择文档保存在当前项目的哪个目录</p>
                </div>
              </md-tab>
            </md-tabs>

            <md-dialog-actions>
              <md-button class="md-primary" @click="onGenerate">Generate</md-button>
              <md-button class="md-primary" @click="showDocDialog = false">Close</md-button>
            </md-dialog-actions>
          </md-dialog>

          <md-button @click="checkDocDialog">Action</md-button>
        </div>
      </md-card>
    </div>
    <div class="md-layout-item">
      <md-card>
        <md-card-area>
          <md-card-media>
            <img src="../../assets/images/code.png" />
          </md-card-media>

          <md-card-header>
            <div class="md-title">提交ISSUES</div>
          </md-card-header>

          <md-card-content>您可通过issues对该项目提出功能需求，反馈bug</md-card-content>
        </md-card-area>
        <div>
          <md-button @click="showCodeDialog">Action</md-button>
        </div>
      </md-card>
    </div>
  </div>
</template>

<script lang="ts">
import { Vue, Component, Prop, Watch } from "vue-property-decorator";
import { getProjectDir,synchronizeRepo, generateDocumentByCoderChain,generateDocument} from "@api/project";
import { Getter, Action } from "vuex-class";
import { UserInterface } from "@/utils/interface";
import baseURL from '@utils/api-url'
import { openLoading, closeLoading } from "@utils/share-variable";
import {
  trim
} from '@utils/index'
@Component
export default class DetailMore extends Vue {
  @Prop() readonly userName!: string;
  @Prop() readonly projectName!: string;
  @Prop() readonly isOwner!: boolean;

  showGitDialog: boolean = false;
  selectGitType: string = "gitee";
  projectToken: string = "";
  projectOwner: string = "";
  repoName: string = "";
  projectID: string = "";
  projectRef: string = "master";
  projectAddress: string = "https://gitee.com";
  projectRoot: string = "";
  
  @Watch("selectGitType")
  onselectRepoTypeChange() {
    // todo...
    this.projectID = "";
    this.projectOwner = "";
    this.repoName = "";
    console.log(this.selectGitType);

    if (this.selectGitType === "gitee") {
      this.projectAddress = "https://gitee.com";
    } else if (this.selectGitType === "gitlab") {
      this.projectAddress = "https://gitlab.com";
    } else if (this.selectGitType === "github") {
      this.projectAddress = "https://github.com";
    }
  }

  onPull(): void {
    if (!this.showGitDialog) {
      return this.$alert("操作非法");
    }
    
    // 数据合法校验
    
    this.selectGitType = trim(this.selectGitType)
    this.projectToken = trim(this.projectToken)
    this.projectRef  = trim(this.projectRef )
    this.projectAddress = trim(this.projectAddress)
    this.projectRoot = trim(this.projectRoot)
    this.projectOwner = trim(this.projectOwner)
    this.repoName = trim(this.repoName)
    this.projectID = trim(this.projectID)

    if (this.selectGitType === "") {
      return this.$alert("请选择仓库类型");
    }

    if (this.projectToken === "") {
      return this.$alert("请输入用户私钥");
    }
    if (this.projectRef === "") {
      return this.$alert("请输入要拉取的项目分支");
    }
    if (this.projectAddress === "") {
      return this.$alert("请输入要拉取的仓库地址");
    }
    if (this.projectRoot === "") {
      return this.$alert("请输入要部署的项目目录");
    }
    if (this.selectGitType == "gitee" && this.projectOwner === "") {
      return this.$alert("请输入创建gitee仓库的用户名");
    }
    if (this.selectGitType == "gitee" && this.repoName === "") {
      return this.$alert("请输入gitee仓库的项目名");
    }
    if (this.selectGitType == "github" && this.projectOwner === "") {
      return this.$alert("请输入github用户名");
    }
    if (this.selectGitType == "github" && this.repoName === "") {
      return this.$alert("请输入相应的github项目名");
    }

    if (this.selectGitType == "gitlab" && this.projectID === "") {
      return this.$alert("请输入正确的项目ID");
    }

    const formData = new FormData();
    formData.append("projectName", this.projectName);
    
    if (this.selectGitType == "gitlab") {
      formData.append("repoType", "0");
    } else if (this.selectGitType == "github") {
      formData.append("repoType", "1");
    } else if (this.selectGitType == "gitee") {
      formData.append("repoType", "2");
    }
    formData.append("repoAddress", this.projectAddress);
    formData.append("repoToken", this.projectToken);
    formData.append("ref", this.projectRef);
    formData.append("projectID", this.projectID);
    formData.append("owner", this.projectOwner);
    formData.append("repoName", this.repoName);
    formData.append("rootPath", this.projectRoot);

    this.showGitDialog = false;
    openLoading("正在拉取中...");

    synchronizeRepo(formData)
      .then(data => {
        closeLoading();
       
        const url = `/detail/code?projectName=${encodeURIComponent(
            this.projectName
          )}&userName=${this.userName}`;
          this.$router.push(url);
        
      })
      .catch(err => {
        closeLoading();
        this.$alert(err);
      });
  }

  showDocDialog: boolean = false;
  selectDocType : string = "coderChain";
  targetUserName : string = "";
  targetProjectName : string = "";
  targetLanguage : string = "java";
  selectLanguage : Array<string> = ["java"];
  documentType : string = "pdf";
  selectDocumentType : Array<string> = ["word","pdf"];
  documentName : string = "";
  otherOption:Array<string> = ["直接下载"]

  @Watch("selectDocType")
  onselectDocTypeChange() {
    // todo...
    this.projectID = "";
    this.projectOwner = "";
    this.repoName = "";
    this.targetUserName = "";
    this.targetProjectName = "";
    if (this.selectDocType === "gitee") {
      this.selectGitType =  "gitee"
      this.projectAddress = "https://gitee.com";
    } else if (this.selectDocType === "gitlab") {
      this.selectGitType =  "gitlab"
      this.projectAddress = "https://gitlab.com";
    } else if (this.selectDocType === "github") {
      this.selectGitType =  "github"
      this.projectAddress = "https://github.com";
    }
  }

  onGenerate():void{
    if (!this.showDocDialog) {
      return this.$alert("操作非法");
    }
    this.selectGitType = trim(this.selectGitType)
    this.projectToken = trim(this.projectToken)
    this.projectRef  = trim(this.projectRef )
    this.projectAddress = trim(this.projectAddress)
    this.projectRoot = trim(this.projectRoot)
    this.projectOwner = trim(this.projectOwner)
    this.repoName = trim(this.repoName)
    this.projectID = trim(this.projectID)
    this.targetLanguage = trim(this.targetLanguage)
    this.documentType = trim(this.documentType)
    this.documentName = trim(this.documentName)
    this.targetProjectName = trim(this.targetProjectName)
    this.targetUserName = trim(this.targetUserName)
    if(this.targetLanguage === "")
    {
      return this.$alert("请选择相关的代码类型");
    }
    if(this.documentType === "")
    {
      return this.$alert("请选择文档类型");
    }
    if(this.documentName === "")
    {
      return this.$alert("请输入将要保存的文档文件名");
    }


    if(this.selectDocType != "coderChain")
    {
      if (this.selectGitType === "") {
        return this.$alert("请选择仓库类型");
      }

      if (this.projectToken === "") {
        return this.$alert("请输入用户私钥");
      }

      if (this.projectRef === "") {
        return this.$alert("请输入要拉取的项目分支");
      }

      if (this.projectAddress === "") {
        return this.$alert("请输入要拉取的仓库地址");
      }

      if (this.selectGitType == "gitee" && this.projectOwner === "") {
        return this.$alert("请输入创建gitee仓库的用户名");
      }

      if (this.selectGitType == "gitee" && this.repoName === "") {
        return this.$alert("请输入gitee仓库的项目名");
      }

      if (this.selectGitType == "github" && this.projectOwner === "") {
        return this.$alert("请输入github用户名");
      }

      if (this.selectGitType == "github" && this.repoName === "") {
        return this.$alert("请输入相应的github项目名");
      }

      if (this.selectGitType == "gitlab" && this.projectID === "") {
        return this.$alert("请输入正确的项目ID");
      }

      const formData = new FormData();
      formData.append("projectName", this.projectName);
      formData.append("targetLanguage",this.targetLanguage);
      formData.append("documentType", this.documentType);
      formData.append("documentName", this.documentName);
      formData.append("rootPath", this.projectRoot);
      formData.append("repoAddress", this.projectAddress);
      formData.append("repoToken", this.projectToken);
      formData.append("ref", this.projectRef);
      formData.append("projectID", this.projectID);
      formData.append("owner", this.projectOwner);
      formData.append("repoName", this.repoName);
      
      if (this.selectGitType == "gitlab") {
        formData.append("repoType", "0");
      } else if (this.selectGitType == "github") {
        formData.append("repoType", "1");
      } else if (this.selectGitType == "gitee") {
        formData.append("repoType", "2");
      }

      this.showDocDialog = false;
      openLoading("正在生成中...");

      generateDocument(formData)
        .then(data => {
          closeLoading();
          if(this.projectRoot == "直接下载")
          {
            const  url = `${baseURL}/api/Api/download?path=${encodeURIComponent(data.path)}&type=${this.documentType}`
            window.open(url,'_blank');
          }
          else
          {
            const url = `/detail/code?projectName=${encodeURIComponent(
              this.projectName
            )}&userName=${this.userName}`;
            this.$router.push(url);
          }
          
          
        })
        .catch(err => {
          closeLoading();
          this.$alert(err);
        });
      
      
    }
    else
    {
      if(this.targetProjectName === "" )
      {
        return this.$alert("请输入目标项目名");
      }
      if(this.targetUserName === "")
      {
        return this.$alert("请输入目标项目所属用户名");
      }
      const formData = new FormData();
      formData.append("projectName", this.projectName);
      formData.append("targetLanguage",this.targetLanguage);
      formData.append("documentType", this.documentType);
      formData.append("documentName", this.documentName);
      formData.append("rootPath", this.projectRoot);
      formData.append("targetUserName", this.targetUserName);
      formData.append("targetProjectName", this.targetProjectName);

      this.showDocDialog = false;
      openLoading("正在拉取中...");

      generateDocumentByCoderChain(formData)
        .then(data => {
          closeLoading();
          if(this.projectRoot == "直接下载")
          {
            const  url = `${baseURL}/api/Api/download?path=${encodeURIComponent(data.path)}&type=${this.documentType}`
            window.open(url,'_blank');
          }
          else
          {
          const url = `/detail/code?projectName=${encodeURIComponent(
              this.projectName
            )}&userName=${this.userName}`;
            this.$router.push(url);
          }
        })
        .catch(err => {
          closeLoading();
          this.$alert(err);
        });
    }
  }


  showCodeDialog(): void{
    const url = `/issues/list/all?projectName=${encodeURIComponent(
              this.projectName
            )}&userName=${this.userName}`;
            this.$router.push(url);
    
  } 

  // 目录列表
  projectdDirList: Array<string> = [];

  created(): void {
    if (!this.projectdDirList.length) {
      console.log("目录不存在，获取");
      getProjectDir(this.userName, this.projectName)
        .then(list => {
          this.projectdDirList = list.dir;
        })
        .catch(err => {
          console.log("获取目录出错：", err);
        });
    }
  }

  checkGitDialog(): void {
    if (!this.isOwner) {
      return this.$alert("权限不足");
    } else {
      this.showGitDialog = true;
    }
  }
  checkDocDialog(): void {
    if (!this.isOwner) {
      return this.$alert("权限不足");
    } else {
      this.showDocDialog = true;
    }
  }
}
</script>
<style lang="stylus" scoped>
.field {
  & > div {
    display: flex;
    align-items: center;
  }

  p {
    opacity: 0.44;
    margin-top: -18px;
  }
}
</style>
<style>
.md-layout-item {
  padding: 16px;
  text-align: center;
}

.md-card {
  margin-top: 16px;
}
.md-card-media img {
  width: 75%;
}
.md-dialog {
  overflow: auto;
  max-height: 90%;
}
</style>
    