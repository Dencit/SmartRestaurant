/* Created by User: soma Worker: 陈鸿扬 on 16/8/15 */
//加载父级js和css

var linkList=window.parent.document.getElementsByTagName("link");//获取父窗口link标签对象列表
var scriptList=window.parent.document.getElementsByTagName("script");//获取父窗口script标签对象列表
var html=document.getElementsByTagName("html").item(0);//外联样式的头部

for(var i=0;i<scriptList.length;i++)
{
    var s=document.createElement("script");
    s.src=scriptList[i].src;
    html.appendChild(s);
}

for(var i=0;i<linkList.length;i++)
{
    var l=document.createElement("link");
    l.rel = 'stylesheet';
    l.type = 'text/css';
    l.href=linkList[i].href;
    html.appendChild(l);
}
//--